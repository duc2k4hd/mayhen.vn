<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingKeepsake extends Model
{
    use HasFactory;

    protected $table = 'wedding_keepsakes';

    protected $fillable = [
        'wedding_id',
        'invitation_id',
        'is_enabled',
        'thank_you_title',
        'thank_you_message',
        'video_url',
        'highlight_photos',
        'show_wishes',
        'show_photos',
        'show_video',
        'hide_rsvp',
        'hide_gifts',
        'anniversary_date',
        'last_exported_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'show_wishes' => 'boolean',
            'show_photos' => 'boolean',
            'show_video' => 'boolean',
            'hide_rsvp' => 'boolean',
            'hide_gifts' => 'boolean',
            'highlight_photos' => 'array',
            'anniversary_date' => 'date',
            'last_exported_at' => 'datetime',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * Số ngày đã trôi qua kể từ ngày cưới.
     */
    public function daysSinceWedding(): int
    {
        $weddingDate = $this->anniversary_date ?: $this->wedding?->wedding_date;
        if (! $weddingDate) {
            return 0;
        }

        $date = Carbon::parse($weddingDate)->startOfDay();
        $now = Carbon::now()->startOfDay();

        return (int) $date->diffInDays($now, false);
    }

    /**
     * Nhãn kỷ niệm (ví dụ: "Kỷ niệm 1 tháng", "Kỷ niệm 1 năm").
     */
    public function getMilestoneLabelAttribute(): string
    {
        $days = $this->daysSinceWedding();
        if ($days < 0) {
            return 'Sắp diễn ra';
        }
        if ($days === 0) {
            return 'Ngày chung đôi hôm nay';
        }
        if ($days < 30) {
            return "Kỷ niệm {$days} ngày chung đôi";
        }
        if ($days < 365) {
            $months = (int) floor($days / 30);

            return "Kỷ niệm {$months} tháng hạnh phúc";
        }
        $years = (int) floor($days / 365);

        return "Kỷ niệm {$years} năm ngày cưới";
    }

    /**
     * Chuẩn hóa URL video YouTube/Vimeo sang dạng Embed iFrame an toàn.
     */
    public function getEmbeddedVideoUrl(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $url = trim($this->video_url);

        // YouTube URL: youtube.com/watch?v=XYZ or youtu.be/XYZ
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        // Vimeo URL: vimeo.com/123456789
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[3];
        }

        return $url;
    }
}
