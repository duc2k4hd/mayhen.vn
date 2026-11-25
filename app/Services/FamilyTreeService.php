<?php

namespace App\Services;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Support\Str;

class FamilyTreeService
{
    public function __construct(
        protected PlanAccessService $planAccess
    ) {}

    /**
     * 4 phong cách mẫu Gia Phả Online cao cấp (Chuẩn Quiet Luxury & Truyền thống Việt).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getTemplates(): array
    {
        return [
            'hoang-kim-tu-duong' => [
                'slug' => 'hoang-kim-tu-duong',
                'name' => 'Hoàng Kim Từ Đường',
                'subtitle' => 'Đỏ Son Cổ Truyền & Ánh Nhũ Vàng',
                'badge' => 'Phổ biến nhất',
                'description' => 'Phù hợp cho từ đường dòng họ, đại gia đình nhiều chi nhánh với đường viền cổ điển, huy hiệu Thủy Tổ và bảng màu trang nghiêm.',
                'primary_color' => '#85182A',
                'accent_color' => '#CFA860',
                'bg_color' => '#FAF5EB',
                'sample_title' => 'Gia Phả Dòng Họ Nguyễn Văn',
                'sample_surname' => 'Nguyễn Văn',
                'sample_hall' => 'Từ Đường Họ Nguyễn • Làng Cổ Loa, Đông Anh, Hà Nội',
                'sample_chronicle' => 'Dòng họ Nguyễn Văn khởi nguồn từ bậc Thủy Tổ đức độ, lấy chữ Nhân – Lễ – Nghĩa – Trí – Tín làm gốc. Trải qua nhiều thế hệ cần cù gây dựng cơ nghiệp, con cháu luôn ghi nhớ công đức tổ tiên, đoàn kết tương trợ và giữ gìn nề nếp gia phong.',
                'sample_rules' => "1. Kính trên nhường dưới, giữ trọn hiếu đạo với ông bà cha mẹ.\n2. Hàng năm vào ngày Giỗ Tổ 15/08 Âm lịch, con cháu các chi hội tụ về Từ Đường dâng hương.\n3. Khuyến học, trọng chữ tín và giúp đỡ người thân lúc khó khăn.",
                'sample_members' => $this->getSampleMembersNguyen(),
            ],
            'thanh-ngoc-gia-phong' => [
                'slug' => 'thanh-ngoc-gia-phong',
                'name' => 'Thanh Ngọc Gia Phong',
                'subtitle' => 'Xanh Ngọc Bích & Kem Lụa Thanh Nhã',
                'badge' => 'Quiet Luxury',
                'description' => 'Phong cách tối giản sang trọng, thoáng mắt cho phả đồ nhiều đời trên cả điện thoại và màn hình máy tính.',
                'primary_color' => '#1E4D3B',
                'accent_color' => '#C5A059',
                'bg_color' => '#F4F7F4',
                'sample_title' => 'Gia Phả Chi Họ Trần Minh',
                'sample_surname' => 'Trần Minh',
                'sample_hall' => 'Nhà Thờ Chi Nhì Họ Trần • Nam Định',
                'sample_chronicle' => 'Ghi chép lại hành trình từ đời Cụ Tổ Trần Minh Đức lập nghiệp tại đất Thành Nam đến các thế hệ con cháu hôm nay, lưu giữ ký ức đẹp và sợi dây kết nối cội nguồn.',
                'sample_rules' => "1. Tôn trọng sự thật lịch sử gia đình, cập nhật kịp thời khi có thành viên mới.\n2. Giữ gìn hòa khí anh em, gắn kết hai bên nội ngoại.",
                'sample_members' => $this->getSampleMembersNguyen('Trần'),
            ],
            'tram-mac-co-dien' => [
                'slug' => 'tram-mac-co-dien',
                'name' => 'Trầm Mặc Cổ Điển',
                'subtitle' => 'Nâu Gỗ Gụ & Giấy Dó Truyền Thống',
                'badge' => 'In Khổ Lớn A0',
                'description' => 'Tái hiện chất liệu giấy dó và gỗ trầm ấm, lý tưởng để xuất file PNG/In khổ lớn treo phòng khách hoặc nhà thờ họ.',
                'primary_color' => '#4E342E',
                'accent_color' => '#B8860B',
                'bg_color' => '#F5EFE2',
                'sample_title' => 'Tộc Phả Họ Lê Đình',
                'sample_surname' => 'Lê Đình',
                'sample_hall' => 'Nhà Thờ Tổ Họ Lê Đình • Thanh Hóa',
                'sample_chronicle' => 'Trăm năm bia đá cũng mòn, nghìn năm gia phả vẫn còn lưu danh. Cuốn tộc phả này lưu giữ tên húy, ngày giỗ và hành trạng của các bậc tiền nhân họ Lê Đình.',
                'sample_rules' => "1. Giữ gìn bản sắc quê hương, trọng nghĩa tình đồng tộc.\n2. Ghi nhớ ngày giỗ các cụ theo Âm lịch hàng năm.",
                'sample_members' => $this->getSampleMembersNguyen('Lê'),
            ],
            'toi-gian-hien-dai' => [
                'slug' => 'toi-gian-hien-dai',
                'name' => 'Tối Giản Hiện Đại',
                'subtitle' => 'Than Chì & Trắng Ngà Tinh Gọn',
                'badge' => 'Gia Đình Trẻ',
                'description' => 'Thiết kế biên tập tạp chí hiện đại, dễ đọc cho các gia đình 3–4 thế hệ muốn lưu giữ ảnh chân dung và câu chuyện gia đình.',
                'primary_color' => '#1C1917',
                'accent_color' => '#C5A880',
                'bg_color' => '#FCFBF9',
                'sample_title' => 'Cây Gia Đình Họ Phạm',
                'sample_surname' => 'Phạm',
                'sample_hall' => 'Gia Đình Đa Thế Hệ • TP. Hồ Chí Minh',
                'sample_chronicle' => 'Lưu giữ hành trình yêu thương của đại gia đình qua 4 thế hệ từ ông bà đến cháu chắt, để thế hệ sau luôn biết mình đến từ đâu.',
                'sample_rules' => "1. Mỗi năm họp mặt đại gia đình ít nhất một lần vào dịp Tết.\n2. Chia sẻ niềm vui, nâng đỡ nhau trong học tập và sự nghiệp.",
                'sample_members' => $this->getSampleMembersNguyen('Phạm'),
            ],
        ];
    }

    public function getTemplate(string $slug): ?array
    {
        $templates = $this->getTemplates();

        return $templates[$slug] ?? null;
    }

    /**
     * Kiểm tra quyền tạo link thật (xuất bản gia phả online):
     * Yêu cầu đã đăng nhập và có gói từ `premium` (Thành Viên) trở lên.
     */
    public function canCreateRealLink(?User $user): bool
    {
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return false;
        }

        return $this->planAccess->hasPlanAtLeast($user, 'premium');
    }

    /**
     * Giới hạn số lượng gia phả thật theo đúng quy chuẩn gói của Mây Hẹn.
     */
    public function getMaxTreesForUser(?User $user): int
    {
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return 0;
        }

        $planSlug = $this->planAccess->getEffectivePlanSlug($user);
        $rule = $this->planAccess->getPlanRule($planSlug);

        return (int) ($rule['max_invitations'] ?? 3);
    }

    /**
     * Tạo slug duy nhất từ tiêu đề gia phả.
     */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'gia-pha-dong-ho';
        $base = Str::limit($base, 80, '');
        $candidate = $base.'-'.Str::lower(Str::random(5));

        while (FamilyTree::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.Str::lower(Str::random(6));
        }

        return $candidate;
    }

    /**
     * Chuẩn hóa và làm sạch mảng thành viên từ Studio trước khi lưu CSDL.
     *
     * @param  array<int, array<string, mixed>>  $rawMembers
     * @return array<int, array<string, mixed>>
     */
    public function sanitizeMembers(array $rawMembers): array
    {
        $cleaned = [];
        $validIds = [];

        foreach (array_slice($rawMembers, 0, 500) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = trim((string) ($item['id'] ?? ''));
            $name = trim(strip_tags((string) ($item['name'] ?? '')));
            if ($id === '' || $name === '') {
                continue;
            }

            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $id) ?: ('m_'.Str::random(6));
            $validIds[$id] = true;

            $avatar = trim((string) ($item['avatar_url'] ?? ''));
            if ($avatar !== '' && ! str_starts_with($avatar, 'data:image/') && ! str_starts_with($avatar, '/')) {
                $avatar = '';
            }
            if (strlen($avatar) > 350000) {
                $avatar = '';
            }

            $cleaned[] = [
                'id' => $id,
                'name' => Str::limit($name, 100, ''),
                'other_name' => Str::limit(trim(strip_tags((string) ($item['other_name'] ?? ''))), 100, ''),
                'gender' => ($item['gender'] ?? 'M') === 'F' ? 'F' : 'M',
                'generation' => max(1, min(30, (int) ($item['generation'] ?? 1))),
                'birth_order' => max(1, min(30, (int) ($item['birth_order'] ?? 1))),
                'is_alive' => (bool) ($item['is_alive'] ?? true),
                'birth_solar' => Str::limit(trim(strip_tags((string) ($item['birth_solar'] ?? ''))), 30, ''),
                'birth_lunar' => Str::limit(trim(strip_tags((string) ($item['birth_lunar'] ?? ''))), 40, ''),
                'death_solar' => Str::limit(trim(strip_tags((string) ($item['death_solar'] ?? ''))), 30, ''),
                'death_lunar' => Str::limit(trim(strip_tags((string) ($item['death_lunar'] ?? ''))), 40, ''),
                'death_anniversary' => Str::limit(trim(strip_tags((string) ($item['death_anniversary'] ?? ''))), 20, ''),
                'hometown' => Str::limit(trim(strip_tags((string) ($item['hometown'] ?? ''))), 120, ''),
                'burial_place' => Str::limit(trim(strip_tags((string) ($item['burial_place'] ?? ''))), 140, ''),
                'occupation' => Str::limit(trim(strip_tags((string) ($item['occupation'] ?? ''))), 100, ''),
                'bio' => Str::limit(trim(strip_tags((string) ($item['bio'] ?? ''))), 600, ''),
                'avatar_url' => $avatar,
                'parent_ids' => array_values(array_filter(array_map('strval', (array) ($item['parent_ids'] ?? [])))),
                'spouse_ids' => array_values(array_filter(array_map('strval', (array) ($item['spouse_ids'] ?? [])))),
            ];
        }

        // Lọc lại tham chiếu ID hợp lệ để tránh liên kết mồ côi
        foreach ($cleaned as &$member) {
            $member['parent_ids'] = array_values(array_filter(
                $member['parent_ids'],
                fn (string $pid): bool => $pid !== $member['id'] && isset($validIds[$pid])
            ));
            $member['spouse_ids'] = array_values(array_filter(
                $member['spouse_ids'],
                fn (string $sid): bool => $sid !== $member['id'] && isset($validIds[$sid])
            ));
        }

        return $cleaned;
    }

    /**
     * Tính thống kê tổng quan cho cây gia phả.
     *
     * @param  array<int, array<string, mixed>>  $members
     * @return array{total_members: int, generations_count: int, living_count: int, memorial_count: int}
     */
    public function computeStats(array $members): array
    {
        $maxGen = 1;
        $living = 0;
        $memorials = 0;

        foreach ($members as $m) {
            $gen = (int) ($m['generation'] ?? 1);
            if ($gen > $maxGen) {
                $maxGen = $gen;
            }
            if (! empty($m['is_alive'])) {
                $living++;
            }
            if (empty($m['is_alive']) && ! empty($m['death_anniversary'])) {
                $memorials++;
            }
        }

        return [
            'total_members' => count($members),
            'generations_count' => $members === [] ? 0 : $maxGen,
            'living_count' => $living,
            'memorial_count' => $memorials,
        ];
    }

    /**
     * Bộ dữ liệu mẫu 4 thế hệ chuẩn Việt Nam (12 thành viên, đầy đủ quan hệ nội-ngoại, ngày giỗ Âm lịch).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSampleMembersNguyen(string $surname = 'Nguyễn'): array
    {
        return [
            [
                'id' => 'm1',
                'name' => $surname.' Văn Khang',
                'other_name' => 'Tự Phúc Thiện (Thủy Tổ)',
                'gender' => 'M',
                'generation' => 1,
                'birth_order' => 1,
                'is_alive' => false,
                'birth_solar' => '1922',
                'birth_lunar' => 'Nhâm Tuất',
                'death_solar' => '1998-09-20',
                'death_lunar' => '15/08 Mậu Dần',
                'death_anniversary' => '15/08',
                'hometown' => 'Đông Anh, Hà Nội',
                'burial_place' => 'Nghĩa trang Từ Đường Cổ Loa',
                'occupation' => 'Nhà Nho • Thầy Đồ',
                'bio' => 'Người đặt nền móng cho Từ Đường dòng họ, nổi tiếng đức độ, trọng học vấn và thương yêu con cháu.',
                'avatar_url' => '',
                'parent_ids' => [],
                'spouse_ids' => ['m2'],
            ],
            [
                'id' => 'm2',
                'name' => 'Đặng Thị Hạnh',
                'other_name' => 'Hiệu Từ Tâm',
                'gender' => 'F',
                'generation' => 1,
                'birth_order' => 1,
                'is_alive' => false,
                'birth_solar' => '1926',
                'birth_lunar' => 'Bính Dần',
                'death_solar' => '2004-04-12',
                'death_lunar' => '23/02 Giáp Thân',
                'death_anniversary' => '23/02',
                'hometown' => 'Bắc Ninh',
                'burial_place' => 'Nghĩa trang Từ Đường Cổ Loa',
                'occupation' => 'Nội trợ • Dệt lụa',
                'bio' => 'Cụ Bà hiền hậu đảm đang, nuôi dạy ba người con trưởng thành và giữ gìn nề nếp gia phong.',
                'avatar_url' => '',
                'parent_ids' => [],
                'spouse_ids' => ['m1'],
            ],
            [
                'id' => 'm3',
                'name' => $surname.' Văn Minh',
                'other_name' => 'Trưởng Nam Đời 2',
                'gender' => 'M',
                'generation' => 2,
                'birth_order' => 1,
                'is_alive' => false,
                'birth_solar' => '1948-05-10',
                'birth_lunar' => 'Mậu Tý',
                'death_solar' => '2019-11-18',
                'death_lunar' => '22/10 Kỷ Hợi',
                'death_anniversary' => '22/10',
                'hometown' => 'Đông Anh, Hà Nội',
                'burial_place' => 'Viên Minh Viên',
                'occupation' => 'Kỹ sư Cầu Đường',
                'bio' => 'Cả đời cống hiến cho ngành giao thông, là người chủ trì trùng tu Nhà Thờ Họ năm 2010.',
                'avatar_url' => '',
                'parent_ids' => ['m1', 'm2'],
                'spouse_ids' => ['m4'],
            ],
            [
                'id' => 'm4',
                'name' => 'Hoàng Thị Lan',
                'other_name' => 'Bà Cả',
                'gender' => 'F',
                'generation' => 2,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '1952-08-14',
                'birth_lunar' => 'Nhâm Thìn',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Đông, Hà Nội',
                'burial_place' => '',
                'occupation' => 'Giáo viên Ngữ Văn (Đã nghỉ hưu)',
                'bio' => 'Người giữ lửa truyền thống hiếu học cho cả chi trưởng.',
                'avatar_url' => '',
                'parent_ids' => [],
                'spouse_ids' => ['m3'],
            ],
            [
                'id' => 'm5',
                'name' => $surname.' Thị Mai',
                'other_name' => 'Trưởng Nữ Đời 2',
                'gender' => 'F',
                'generation' => 2,
                'birth_order' => 2,
                'is_alive' => true,
                'birth_solar' => '1954-03-21',
                'birth_lunar' => 'Giáp Ngọ',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Đông Anh, Hà Nội',
                'burial_place' => '',
                'occupation' => 'Bác sĩ Nhi Khoa',
                'bio' => 'Tận tâm với nghề y, thường xuyên tổ chức khám sức khỏe cho người cao tuổi trong họ.',
                'avatar_url' => '',
                'parent_ids' => ['m1', 'm2'],
                'spouse_ids' => [],
            ],
            [
                'id' => 'm6',
                'name' => $surname.' Văn Hùng',
                'other_name' => 'Thứ Nam Đời 2',
                'gender' => 'M',
                'generation' => 2,
                'birth_order' => 3,
                'is_alive' => true,
                'birth_solar' => '1958-12-02',
                'birth_lunar' => 'Mậu Tuất',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Đông Anh, Hà Nội',
                'burial_place' => '',
                'occupation' => 'Doanh nhân',
                'bio' => 'Phụ trách Quỹ Khuyến Học của dòng họ.',
                'avatar_url' => '',
                'parent_ids' => ['m1', 'm2'],
                'spouse_ids' => ['m7'],
            ],
            [
                'id' => 'm7',
                'name' => 'Vũ Thị Ngọc',
                'other_name' => '',
                'gender' => 'F',
                'generation' => 2,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '1961-06-15',
                'birth_lunar' => 'Tân Sửu',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hải Phòng',
                'burial_place' => '',
                'occupation' => 'Kế toán trưởng',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => [],
                'spouse_ids' => ['m6'],
            ],
            [
                'id' => 'm8',
                'name' => $surname.' Hoàng Nam',
                'other_name' => 'Trưởng Tộc Đời 3',
                'gender' => 'M',
                'generation' => 3,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '1978-09-09',
                'birth_lunar' => 'Mậu Ngọ',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Nội',
                'burial_place' => '',
                'occupation' => 'Kiến trúc sư',
                'bio' => 'Người khởi xướng số hóa gia phả online để kết nối các thế hệ con cháu ở nước ngoài.',
                'avatar_url' => '',
                'parent_ids' => ['m3', 'm4'],
                'spouse_ids' => ['m9'],
            ],
            [
                'id' => 'm9',
                'name' => 'Trần Minh Anh',
                'other_name' => '',
                'gender' => 'F',
                'generation' => 3,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '1981-02-19',
                'birth_lunar' => 'Tân Dậu',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'TP. Hồ Chí Minh',
                'burial_place' => '',
                'occupation' => 'Giảng viên Đại học',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => [],
                'spouse_ids' => ['m8'],
            ],
            [
                'id' => 'm10',
                'name' => $surname.' Thu Hà',
                'other_name' => '',
                'gender' => 'F',
                'generation' => 3,
                'birth_order' => 2,
                'is_alive' => true,
                'birth_solar' => '1983-11-04',
                'birth_lunar' => 'Quý Hợi',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Nội',
                'burial_place' => '',
                'occupation' => 'Chuyên gia Tài chính',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => ['m3', 'm4'],
                'spouse_ids' => [],
            ],
            [
                'id' => 'm11',
                'name' => $surname.' Quốc Bảo',
                'other_name' => '',
                'gender' => 'M',
                'generation' => 3,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '1988-07-22',
                'birth_lunar' => 'Mậu Thìn',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Nội',
                'burial_place' => '',
                'occupation' => 'Kỹ sư Phần mềm',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => ['m6', 'm7'],
                'spouse_ids' => [],
            ],
            [
                'id' => 'm12',
                'name' => $surname.' Gia Huy',
                'other_name' => 'Đời Thứ 4',
                'gender' => 'M',
                'generation' => 4,
                'birth_order' => 1,
                'is_alive' => true,
                'birth_solar' => '2008-10-15',
                'birth_lunar' => 'Mậu Tý',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Nội',
                'burial_place' => '',
                'occupation' => 'Học sinh Chuyên Tin',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => ['m8', 'm9'],
                'spouse_ids' => [],
            ],
            [
                'id' => 'm13',
                'name' => $surname.' Bảo Ngọc',
                'other_name' => 'Đời Thứ 4',
                'gender' => 'F',
                'generation' => 4,
                'birth_order' => 2,
                'is_alive' => true,
                'birth_solar' => '2012-04-06',
                'birth_lunar' => 'Nhâm Thìn',
                'death_solar' => '',
                'death_lunar' => '',
                'death_anniversary' => '',
                'hometown' => 'Hà Nội',
                'burial_place' => '',
                'occupation' => 'Học sinh',
                'bio' => '',
                'avatar_url' => '',
                'parent_ids' => ['m8', 'm9'],
                'spouse_ids' => [],
            ],
        ];
    }
}
