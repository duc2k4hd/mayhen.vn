<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Str;

class TwoFamilyApprovalService
{
    /**
     * Lấy hoặc tạo mới liên kết phê duyệt thiệp cưới dành riêng cho Nhà Trai và Nhà Gái.
     *
     * @return array{
     *     groom_family: array<string, mixed>,
     *     bride_family: array<string, mixed>,
     *     all_approved: bool
     * }
     */
    public function getOrCreateApprovalTokens(Invitation $invitation): array
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $approvals = $config['family_approvals'] ?? [];
        $needsSave = false;

        if (empty($approvals['groom_family']['token'])) {
            $approvals['groom_family'] = [
                'token' => 'gf_'.Str::random(32),
                'status' => 'pending',
                'approved_at' => null,
                'feedback' => null,
                'feedback_at' => null,
                'blessing_note' => null,
            ];
            $needsSave = true;
        }

        if (empty($approvals['bride_family']['token'])) {
            $approvals['bride_family'] = [
                'token' => 'bf_'.Str::random(32),
                'status' => 'pending',
                'approved_at' => null,
                'feedback' => null,
                'feedback_at' => null,
                'blessing_note' => null,
            ];
            $needsSave = true;
        }

        if ($needsSave) {
            $config['family_approvals'] = $approvals;
            $invitation->custom_config = $config;
            $invitation->save();
        }

        $groomApproved = ($approvals['groom_family']['status'] ?? '') === 'approved';
        $brideApproved = ($approvals['bride_family']['status'] ?? '') === 'approved';

        return [
            'groom_family' => array_merge($approvals['groom_family'], [
                'side' => 'groom_family',
                'label' => 'Nhà Trai (Bố Mẹ Chú Rể)',
                'review_url' => route('family_approval.review', ['token' => $approvals['groom_family']['token']]),
            ]),
            'bride_family' => array_merge($approvals['bride_family'], [
                'side' => 'bride_family',
                'label' => 'Nhà Gái (Bố Mẹ Cô Dâu)',
                'review_url' => route('family_approval.review', ['token' => $approvals['bride_family']['token']]),
            ]),
            'all_approved' => $groomApproved && $brideApproved,
        ];
    }

    /**
     * Tìm thiệp cưới và phân nhánh gia đình dựa trên token.
     *
     * @return array{invitation: Invitation, side: string, data: array<string, mixed>}|null
     */
    public function findByToken(string $token): ?array
    {
        $invitations = Invitation::with('wedding')
            ->whereNotNull('custom_config')
            ->get();

        foreach ($invitations as $invitation) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?? []);

            $approvals = $config['family_approvals'] ?? [];

            if (($approvals['groom_family']['token'] ?? '') === $token) {
                return [
                    'invitation' => $invitation,
                    'side' => 'groom_family',
                    'data' => $approvals['groom_family'],
                ];
            }

            if (($approvals['bride_family']['token'] ?? '') === $token) {
                return [
                    'invitation' => $invitation,
                    'side' => 'bride_family',
                    'data' => $approvals['bride_family'],
                ];
            }
        }

        return null;
    }

    /**
     * Lấy toàn bộ thông tin ngày cưới định dạng dành riêng cho phụ mẫu rà soát.
     *
     * @return array<string, mixed>
     */
    public function getFamilyReviewData(string $token): array
    {
        $found = $this->findByToken($token);
        if (! $found) {
            abort(404, 'Liên kết rà soát gia đình không tồn tại hoặc đã hết hạn.');
        }

        $invitation = $found['invitation'];
        $wedding = $invitation->wedding;
        $side = $found['side'];
        $sideData = $found['data'];

        $extra = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode((string) $wedding->extra_data, true) ?? []);

        // Phụ mẫu Nhà Trai
        $groomFather = $extra['groom_father'] ?? '';
        $groomMother = $extra['groom_mother'] ?? '';
        $groomParents = trim($wedding->groom_parents ?? '');
        if (empty($groomParents) && ($groomFather || $groomMother)) {
            $groomParents = trim(($groomFather ? "Ông: {$groomFather}" : '').($groomMother ? " — Bà: {$groomMother}" : ''));
        }

        // Phụ mẫu Nhà Gái
        $brideFather = $extra['bride_father'] ?? '';
        $brideMother = $extra['bride_mother'] ?? '';
        $brideParents = trim($wedding->bride_parents ?? '');
        if (empty($brideParents) && ($brideFather || $brideMother)) {
            $brideParents = trim(($brideFather ? "Ông: {$brideFather}" : '').($brideMother ? " — Bà: {$brideMother}" : ''));
        }

        // Lịch trình lễ gia tiên
        $ceremonyGroomTime = $extra['ceremony_groom_time'] ?? '';
        $ceremonyGroomDate = $extra['ceremony_groom_date'] ?? '';
        $ceremonyBrideTime = $extra['ceremony_bride_time'] ?? '';
        $ceremonyBrideDate = $extra['ceremony_bride_date'] ?? '';

        $sideLabel = $side === 'groom_family' ? 'Gia Đình Nhà Trai (Bố Mẹ Chú Rể)' : 'Gia Đình Nhà Gái (Bố Mẹ Cô Dâu)';

        return [
            'side' => $side,
            'side_label' => $sideLabel,
            'is_groom_side' => $side === 'groom_family',
            'token' => $token,
            'status' => $sideData['status'] ?? 'pending',
            'approved_at' => $sideData['approved_at'] ?? null,
            'feedback' => $sideData['feedback'] ?? null,
            'feedback_at' => $sideData['feedback_at'] ?? null,
            'blessing_note' => $sideData['blessing_note'] ?? null,
            'wedding' => [
                'groom_name' => $wedding->groom_name ?? 'Chú Rể',
                'bride_name' => $wedding->bride_name ?? 'Cô Dâu',
                'groom_parents' => $groomParents ?: 'Chưa cập nhật',
                'bride_parents' => $brideParents ?: 'Chưa cập nhật',
                'wedding_date' => $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'Chưa đặt',
                'venue_name' => $wedding->venue_name ?? 'Tư gia',
                'venue_address' => $wedding->venue_address ?? '',
                'ceremony_groom' => trim("{$ceremonyGroomTime} {$ceremonyGroomDate}") ?: 'Theo thông lệ gia đình',
                'ceremony_bride' => trim("{$ceremonyBrideTime} {$ceremonyBrideDate}") ?: 'Theo thông lệ gia đình',
            ],
            'invitation_preview_url' => route('invitation.show', ['slug' => $invitation->slug]),
        ];
    }

    /**
     * Ghi nhận quyết định của phụ mẫu (Phê duyệt hoặc Gửi góp ý chỉnh sửa).
     *
     * @return array<string, mixed>
     */
    public function submitDecision(string $token, string $decision, ?string $note = null): array
    {
        $found = $this->findByToken($token);
        if (! $found) {
            abort(404, 'Không tìm thấy hồ sơ rà soát gia đình.');
        }

        $invitation = $found['invitation'];
        $side = $found['side'];

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $approvals = $config['family_approvals'] ?? [];
        $sideData = $approvals[$side] ?? ['token' => $token];

        if ($decision === 'approved') {
            $sideData['status'] = 'approved';
            $sideData['approved_at'] = now()->toIso8601String();
            $sideData['blessing_note'] = $note ? trim($note) : 'Bố mẹ đã xem kỹ thông tin hôn lễ và hoàn toàn đồng ý. Chúc hai con hạnh phúc viên mãn!';
            $sideData['feedback'] = null;
            $sideData['feedback_at'] = null;
        } elseif ($decision === 'feedback') {
            $sideData['status'] = 'feedback';
            $sideData['feedback'] = $note ? trim($note) : 'Có một số chi tiết cần chỉnh sửa lại trước khi phát hành thiệp.';
            $sideData['feedback_at'] = now()->toIso8601String();
            $sideData['approved_at'] = null;
        }

        $approvals[$side] = $sideData;
        $config['family_approvals'] = $approvals;
        $invitation->custom_config = $config;
        $invitation->save();

        return [
            'success' => true,
            'side' => $side,
            'status' => $sideData['status'],
            'approved_at' => $sideData['approved_at'] ?? null,
            'feedback' => $sideData['feedback'] ?? null,
            'blessing_note' => $sideData['blessing_note'] ?? null,
        ];
    }
}
