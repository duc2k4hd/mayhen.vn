<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WeddingEventService
{
    /**
     * Lấy danh sách sự kiện của đám cưới, tự động khởi tạo mặc định nếu chưa có.
     */
    public function getEventsForWedding(Wedding $wedding): Collection
    {
        $events = $wedding->events()->get();

        if ($events->isEmpty() && $wedding->wedding_date) {
            $wedding->syncEventsFromPrimaryDetails();

            return $wedding->events()->get();
        }

        return $events;
    }

    /**
     * Tạo mới một sự kiện con trong đám cưới.
     */
    public function createEvent(Wedding $wedding, array $data, ?int $actorId = null): WeddingEvent
    {
        $maxOrder = (int) $wedding->events()->max('display_order');

        return DB::transaction(function () use ($wedding, $data, $maxOrder, $actorId) {
            $event = $wedding->events()->create([
                'name' => trim($data['name']),
                'event_type' => $data['event_type'] ?? 'banquet',
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'] ?? null,
                'lunar_date' => $data['lunar_date'] ?? null,
                'venue_name' => trim($data['venue_name']),
                'venue_address' => $data['venue_address'] ?? null,
                'map_url' => $data['map_url'] ?? null,
                'dress_code' => $data['dress_code'] ?? null,
                'description' => $data['description'] ?? null,
                'display_order' => $data['display_order'] ?? ($maxOrder + 1),
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            AuditLogger::log(
                'WEDDING_EVENT_CREATED',
                'wedding_event',
                $event->id,
                "Tạo sự kiện '{$event->name}' cho đám cưới #{$wedding->id}",
                [
                    'actor_id' => $actorId,
                    'wedding_id' => $wedding->id,
                    'event_name' => $event->name,
                    'event_type' => $event->event_type,
                    'event_date' => $event->event_date->format('Y-m-d'),
                ]
            );

            return $event;
        });
    }

    /**
     * Cập nhật sự kiện.
     */
    public function updateEvent(WeddingEvent $event, array $data, ?int $actorId = null): WeddingEvent
    {
        return DB::transaction(function () use ($event, $data, $actorId) {
            $event->update([
                'name' => trim($data['name'] ?? $event->name),
                'event_type' => $data['event_type'] ?? $event->event_type,
                'event_date' => $data['event_date'] ?? $event->event_date,
                'event_time' => $data['event_time'] ?? $event->event_time,
                'lunar_date' => $data['lunar_date'] ?? $event->lunar_date,
                'venue_name' => trim($data['venue_name'] ?? $event->venue_name),
                'venue_address' => $data['venue_address'] ?? $event->venue_address,
                'map_url' => $data['map_url'] ?? $event->map_url,
                'dress_code' => $data['dress_code'] ?? $event->dress_code,
                'description' => $data['description'] ?? $event->description,
                'display_order' => isset($data['display_order']) ? (int) $data['display_order'] : $event->display_order,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $event->is_active,
            ]);

            AuditLogger::log(
                'WEDDING_EVENT_UPDATED',
                'wedding_event',
                $event->id,
                "Cập nhật sự kiện '{$event->name}' trong đám cưới #{$event->wedding_id}",
                [
                    'actor_id' => $actorId,
                    'wedding_id' => $event->wedding_id,
                    'event_name' => $event->name,
                ]
            );

            return $event->fresh();
        });
    }

    /**
     * Xóa sự kiện.
     */
    public function deleteEvent(WeddingEvent $event, ?int $actorId = null): bool
    {
        return DB::transaction(function () use ($event, $actorId) {
            $weddingId = $event->wedding_id;
            $eventName = $event->name;
            $eventId = $event->id;

            $deleted = $event->delete();

            if ($deleted) {
                AuditLogger::log(
                    'WEDDING_EVENT_DELETED',
                    'wedding_event',
                    $eventId,
                    "Xóa sự kiện '{$eventName}' khỏi đám cưới #{$weddingId}",
                    [
                        'actor_id' => $actorId,
                        'wedding_id' => $weddingId,
                        'event_name' => $eventName,
                    ]
                );
            }

            return (bool) $deleted;
        });
    }

    /**
     * Sắp xếp lại thứ tự các sự kiện.
     */
    public function reorderEvents(Wedding $wedding, array $eventIdsOrdered): void
    {
        DB::transaction(function () use ($wedding, $eventIdsOrdered) {
            foreach ($eventIdsOrdered as $index => $id) {
                $wedding->events()->where('id', $id)->update(['display_order' => $index + 1]);
            }
        });
    }

    /**
     * Xuất payload danh sách sự kiện để nhúng Studio và trang thiệp.
     */
    public function getPublicEventsPayload(Wedding $wedding): array
    {
        $events = $wedding->getActiveEvents();

        return $events->map(function (WeddingEvent $e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'event_type' => $e->event_type,
                'type_label' => $e->type_label,
                'event_date' => $e->event_date ? $e->event_date->format('Y-m-d') : null,
                'event_date_formatted' => $e->event_date ? $e->event_date->format('d/m/Y') : null,
                'event_time' => $e->event_time,
                'formatted_time' => $e->formatted_date_time,
                'lunar_date' => $e->lunar_date,
                'venue_name' => $e->venue_name,
                'venue_address' => $e->venue_address,
                'map_url' => $e->map_url,
                'dress_code' => $e->dress_code,
                'description' => $e->description,
                'display_order' => $e->display_order,
                'is_past' => $e->isPast(),
            ];
        })->toArray();
    }
}
