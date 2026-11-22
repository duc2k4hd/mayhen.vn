<?php

namespace App\Services;

class TemplateLaunchNewsSpecs
{
    /**
     * Đặc tả kỹ thuật & mỹ thuật chi tiết, chính xác theo đúng mã nguồn từng mẫu trong 23 mẫu thiệp cưới Mây Hẹn.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'giua-muon-ngan-vi-sao' => [
                'theme_palette' => 'Xanh Đêm Thiên Hà, Vàng Ánh Sao & Trắng Ngà',
                'opening_effect' => 'Lớp nền Canvas sao rơi & hạt sáng lấp lánh tương tác mượt mà ngay từ khoảnh khắc khách mở thiệp',
                'signature_parts' => [
                    ['name' => 'Trang bìa Hero & Không gian sao rơi', 'detail' => 'Nền trời đêm sâu thẳm điểm xuyết các hạt sao rơi tự động bằng Canvas thuần, làm nổi bật tên cô dâu chú rể và ngày trọng đại.'],
                    ['name' => 'Đồng hồ đếm ngược thiên hà & Tờ lịch tháng sao rơi', 'detail' => 'Bộ đếm ngược từng giây theo phong cách thiên văn kết hợp tờ lịch tháng cưới đánh dấu ngày hạnh phúc.'],
                    ['name' => 'Thư mời tiệc cưới, Lễ Gia Tiên & Bản đồ 3D cánh mở', 'detail' => 'Tách rõ thẻ Thư Mời Tiệc Cưới chính, khối Nghi Lễ Gia Tiên (Vu Quy & Thành Hôn) và khung Bản Đồ Chỉ Đường 3D mở cánh ấn tượng, có hỗ trợ phân tuyến lịch trình theo từng nhóm khách.'],
                    ['name' => 'Album ảnh Coverflow, Dresscode & Sổ ký tên chúc phúc', 'detail' => 'Trình diễn 10 ảnh cưới dạng Coverflow mượt mà, bảng màu quy định trang phục, sổ ký tên tay điện tử, RSVP và mã mừng cưới VietQR.'],
                ],
                'best_for' => 'Cặp đôi tổ chức tiệc cưới buổi tối tại trung tâm hội nghị sang trọng, thích cảm giác lãng mạn huyền ảo như dưới bầu trời sao và bộ ảnh cưới tông trắng – đen – ánh kim.',
            ],
            'hac-bach-trao-duyen' => [
                'theme_palette' => 'Đen Tuyền, Trắng Tinh Khôi, Hồng Bảo Bordeaux & Vàng Ánh Kim',
                'opening_effect' => 'Nút niêm phong Tem Sáp (Wax Seal) cổ điển kết hợp thanh điều khiển Đĩa Than Vinyl nổi có sóng âm mini (Mini Soundwave)',
                'signature_parts' => [
                    ['name' => 'Đĩa than Vinyl nổi & Sóng âm mini', 'detail' => 'Nút phát nhạc cưới dạng đĩa than quay tròn kèm hiệu ứng sóng âm nhảy nhịp theo nhạc và cụm nút thao tác nhanh nổi gọn bên cạnh.'],
                    ['name' => 'Hero Editorial & Khung vòm đôi (Couple Arch Frame)', 'detail' => 'Bố cục tương phản đen trắng chuẩn tạp chí thời trang, dẫn dắt bằng mũi tên cuộn trang tinh tế và khung vòm đôi tôn vinh ảnh cưới.'],
                    ['name' => 'Save The Date, Lịch tháng động & Đồng hồ đếm ngược', 'detail' => 'Tờ lịch tháng cưới tự động khớp đúng ngày cưới của hai bạn đi kèm đồng hồ đếm ngược sắc nét.'],
                    ['name' => 'Thông tin 2 họ, Dresscode & Bộ tương tác Ký tên — RSVP — VietQR', 'detail' => 'Trình bày thông tin song thân hai họ chuẩn mực, bảng màu trang phục Bordeaux – Đen – Kem cùng sổ ký tên và mừng cưới VietQR minh bạch.'],
                ],
                'best_for' => 'Cặp đôi yêu phong cách Editorial hiện đại, chụp ảnh cưới đen trắng hoặc studio ánh sáng nghệ thuật, muốn tấm thiệp mang khí chất thời thượng và khác biệt.',
            ],
            'hanh-phuc-mang-ten-chung-minh' => [
                'theme_palette' => 'Xanh Xô Thơm (Sage Green), Kem Lụa & Vàng Đồng Nhẹ',
                'opening_effect' => 'Cánh cửa mở đôi Song Hỷ kết hợp hiệu ứng lá Xô Thơm (Sage) rơi nhẹ 60fps xuyên suốt trang thiệp',
                'signature_parts' => [
                    ['name' => 'Màn mở cánh cửa đôi & Nút đĩa than góc trái', 'detail' => 'Mở đầu bằng hai cánh cửa khép hờ mở bung êm ái, đi kèm nút đĩa than bật/tắt nhạc cố định ở góc trên bên trái.'],
                    ['name' => 'Lời ngỏ, Câu chuyện tình yêu & Song thân hai họ', 'detail' => 'Bố cục mạch lạc từng phần rõ ràng: trang bìa Hero, lời ngỏ yêu thương, song thân hai bên gia đình và đôi uyên ương.'],
                    ['name' => 'Đầy đủ 4 khung sự kiện: Lễ Ăn Hỏi, Lễ Thành Hôn, Tiệc Nhà Gái & Tiệc Nhà Trai', 'detail' => 'Cấu trúc cực kỳ thực tế cho đám cưới Việt Nam có nhiều nghi lễ, kèm khung bản đồ Google Maps tương tác trực tiếp.'],
                    ['name' => 'Lịch tháng động, Sổ lưu bút chữ ký & Hộp quà VietQR', 'detail' => 'Tờ lịch tháng tự động đồng bộ ngày cưới, sổ lưu bút ký tên tay nghệ thuật, xác nhận RSVP và mã VietQR tiện lợi.'],
                ],
                'best_for' => 'Cặp đôi chuộng sự thanh nhã, nhẹ nhàng chuẩn Quiet Luxury, chụp ảnh cưới ngoài trời hoặc phim trường nhiều cây xanh và cần ghi rõ nhiều nghi lễ của hai họ.',
            ],
            'ngay-tinh-yeu-dom-hoa' => [
                'theme_palette' => 'Đỏ Son Hoàng Gia, Kem Giấy Mỹ Thuật & Vàng Ánh Kim',
                'opening_effect' => 'Cánh cửa đôi Song Hỷ mở bung hai bên kèm hiệu ứng hoa tuyết đỏ rơi nhẹ và nút tự động cuộn thiệp (Auto-Scroll)',
                'signature_parts' => [
                    ['name' => 'Cánh cửa đôi Song Hỷ & Tiện ích Auto-Scroll', 'detail' => 'Mở thiệp bằng cánh cửa đỏ son trang trọng; tích hợp cả nút đĩa than nhạc và nút tự động cuộn êm ái để khách lớn tuổi chỉ cần ngồi xem trọn tấm thiệp.'],
                    ['name' => 'Trang bìa Hero đầy đủ nghi lễ & Khung ảnh chính sắc nét', 'detail' => 'Hiển thị rõ loại hình lễ cưới, tên đôi uyên ương, khung ảnh chính, ngày cưới và lời slogan lãng mạn.'],
                    ['name' => 'Lời ngỏ, Chuyện tình yêu & Bộ ba ảnh nghệ thuật', 'detail' => 'Trình bày câu chuyện quen nhau kết hợp cụm ảnh cưới ấn tượng trên nền đỏ kem hài hòa.'],
                    ['name' => 'Thông tin tiệc, Bản đồ, Sổ ký tên, RSVP & VietQR', 'detail' => 'Đầy đủ bản đồ chỉ đường, bảng màu trang phục, sổ ký tên tay, xác nhận tham dự và mừng cưới VietQR không thu phí.'],
                ],
                'best_for' => 'Cặp đôi yêu nét truyền thống sang trọng, chụp ảnh áo dài cưới đỏ hoặc lễ gia tiên ấm cúng, muốn một tấm thiệp vừa làm hài lòng ba mẹ vừa hiện đại với bạn bè.',
            ],
            'tron-kiep-nay-ben-nhau' => [
                'theme_palette' => 'Đỏ Son Truyền Thống, Vàng Ánh Kim & Kem Cổ Điển',
                'opening_effect' => 'Màn mở cánh cửa Song Hỷ đỏ son uy nghi dẫn vào trang bìa đậm chất hỷ sự truyền thống Việt Nam',
                'signature_parts' => [
                    ['name' => 'Khung vòm ảnh kỷ niệm & Song thân hai họ tách dòng chuẩn mực', 'detail' => 'Phần lời ngỏ đi kèm khung vòm nghệ thuật tôn dáng ảnh cưới, tiếp nối là dòng Nhà Trai – Nhà Gái cân đối dễ đọc.'],
                    ['name' => 'Tờ lịch tháng & Đồng hồ đếm ngược hình trái tim', 'detail' => 'Tờ lịch tháng cưới đồng bộ tự động đứng cạnh cụm đồng hồ đếm ngược hình trái tim ánh kim nổi bật.'],
                    ['name' => 'Bộ 3 ảnh vòm nghệ thuật áo dài đỏ Song Hỷ', 'detail' => 'Điểm nhấn thị giác đắt giá ngay trước phần lời mời quan khách và thông tin hôn lễ cử hành tại tư gia.'],
                    ['name' => 'Lịch trình lễ cưới, Dresscode, Album 10 ảnh & VietQR', 'detail' => 'Trọn bộ lịch trình từng khung giờ, bảng màu trang phục, album ảnh lớn, sổ ký tên và xác nhận tham dự RSVP.'],
                ],
                'best_for' => 'Cặp đôi có bộ ảnh áo dài đỏ truyền thống hoặc lễ rước dâu trang trọng, muốn giữ trọn không khí hỷ sự rực rỡ nhưng vẫn gọn gàng, tinh tế.',
            ],
            'vom-troi-may-ao-cuoi' => [
                'theme_palette' => 'Xanh Rêu Thực Vật (Botanical Sage), Kem Lụa & Nâu Gỗ Tự Nhiên',
                'opening_effect' => 'Màn mở Phong Bì Sáp 3D (Envelope Opening Curtain) kết hợp cánh hoa xanh rêu và đốm sáng rơi nhẹ nhàng',
                'signature_parts' => [
                    ['name' => 'Trang bìa Hero & Đĩa than Vinyl đặt chồng nghệ thuật', 'detail' => 'Đĩa than Turntable Vinyl xoay trực tiếp ngay giao điểm trang bìa tạo dấu ấn âm nhạc hoài cổ cực cuốn hút.'],
                    ['name' => 'Chân dung so le & Thư mời song thân hai họ', 'detail' => 'Thẻ chân dung cô dâu chú rể xếp so le tự nhiên bên cạnh thư báo hỷ và thông tin hai bên gia đình.'],
                    ['name' => 'Câu chuyện tình yêu kiểu ảnh Polaroid kẹp gỗ', 'detail' => 'Kể lại từng cột mốc yêu nhau qua những khung ảnh Polaroid mộc mạc, tạo cảm giác gần gũi như cuốn sổ tay kỷ niệm.'],
                    ['name' => 'Lịch tháng, Lịch trình, Album Coverflow 3D & Hộp quà VietQR', 'detail' => 'Đầy đủ ngày chung đôi, lịch trình lễ tiệc, album 3D vuốt mượt, sổ ký tên không ảnh Stock và hộp quà tương tác VietQR.'],
                ],
                'best_for' => 'Cặp đôi chụp ảnh cưới ngoại cảnh Đà Lạt, sân vườn hoặc phong cách Rustic – Botanical nhẹ nhàng, yêu âm nhạc và những chi tiết thủ công tinh tế.',
            ],
            'mayhen-curated' => [
                'theme_palette' => 'Đa Bảng Màu Tự Phối Thông Minh (Kem Lụa, Vàng Champagne, Xanh Rêu, Hồng Phấn, Đỏ Rượu)',
                'opening_effect' => 'Màn mở Phong Bì Nghệ Thuật (Opening Envelope Gate) kết hợp trình diễn AI Atelier dựng từng lớp nền và từng phần tử trực tiếp',
                'signature_parts' => [
                    ['name' => 'Công nghệ tự phối bố cục & bảng màu theo gu riêng', 'detail' => 'Không gò bó trong một khuôn cố định, hai bạn chọn phong cách và bảng màu yêu thích để hệ thống tự phối typography, viền và sắc độ hài hòa.'],
                    ['name' => 'Trình diễn AI tạo thiệp trực quan từng bước', 'detail' => 'Tích hợp hoàn hảo với công cụ Tạo Thiệp Tự Động AI (/tao-thiep-tu-dong), cho phép phối lại biến thể mới chỉ trong 1 chạm.'],
                    ['name' => 'Cấu trúc 7+ phần tinh gọn chuẩn Quiet Luxury', 'detail' => 'Đầy đủ Trang bìa, Lời ngỏ & Chuyện tình yêu, Song thân hai họ, Đếm ngược & Lịch tháng, Sự kiện & Bản đồ, Lịch trình hôn lễ và Album 10 ảnh.'],
                    ['name' => 'Đĩa than nhạc nền, RSVP, Sổ ký tên & VietQR đồng bộ', 'detail' => 'Tích hợp sẵn nút đĩa than bật/tắt nhạc và trọn bộ tương tác khách mời cao cấp thuộc đặc quyền Gói Pro VIP.'],
                ],
                'best_for' => 'Cặp đôi bận rộn muốn có một tấm thiệp mang bản sắc riêng không đụng hàng, thích trải nghiệm AI gợi ý bố cục và phối màu tự động trong vài phút.',
            ],
            'ban-sonata-mau-hanh-phuc' => [
                'theme_palette' => 'Xanh Ô Liu (Olive Sage), Giấy Kem Mỹ Thuật & Vàng Đồng Cổ Điển',
                'opening_effect' => 'Màn mở khung Oval rèm voan mềm mại kết hợp lớp lá ô liu rơi nhẹ xuyên suốt hành trình xem thiệp',
                'signature_parts' => [
                    ['name' => 'Đầu thiệp & Save The Date thanh tao', 'detail' => 'Bố cục mở đầu như bìa một bản nhạc cổ điển, dẫn nhịp êm ái sang khu vực Save The Date và ảnh bìa chính.'],
                    ['name' => 'Lịch tháng & Đồng hồ đếm ngược Huy Chương Tròn V2', 'detail' => 'Cụm đồng hồ đếm ngược thiết kế dạng huy chương tròn sắc sảo nằm cân đối cạnh tờ lịch tháng cưới.'],
                    ['name' => 'Thẻ sự kiện Vé Mời & Bản đồ Bưu Thiếp Hàng Không', 'detail' => 'Thông tin tiệc cưới thiết kế như tấm vé mời danh dự, đi kèm khung bản đồ kiểu bưu thiếp hàng không viền sọc cổ điển cực độc đáo.'],
                    ['name' => 'Dresscode Cửa Sổ Vòm Hoa & Album Coverflow 3D', 'detail' => 'Bảng màu trang phục đặt trong khung cửa sổ vòm hoa lá ô liu, tiếp nối là Album 10 ảnh 3D Coverflow, sổ ký tên, RSVP và hộp quà VietQR.'],
                ],
                'best_for' => 'Cặp đôi tổ chức tiệc cưới sân vườn, khách sạn boutique hoặc chụp ảnh cưới châu Âu cổ điển — một trong những thiết kế Hoàng Gia Pro được yêu thích nhất trên Mây Hẹn.',
            ],
            'xuan-sang-tinh-vua-chom' => [
                'theme_palette' => 'Giấy Dó Mộc Mạc, Đỏ Son Hoa Đào & Xanh Ô Liu Cổ Phục',
                'opening_effect' => 'Tấm thiệp mở Imperial Blossom Card điểm xuyết hoa đào xuân, đèn lồng đỏ và cánh hoa rơi nhẹ đầy thi vị',
                'signature_parts' => [
                    ['name' => 'Trang bìa Song Hỷ & Hoa Đào Xuân trên nền giấy dó', 'detail' => 'Tái hiện chất liệu giấy dó truyền thống Việt Nam kết hợp chữ Song Hỷ lớn, hoa đào và khung ảnh cô dâu chú rể nổi bật.'],
                    ['name' => 'Phân tách màu sắc Nghi Lễ (Xanh Ô Liu) & Tiệc Cưới (Đỏ Son)', 'detail' => 'Khối Lễ Gia Tiên mang tông xanh ô liu trang nghiêm tách biệt rõ với khối Tiệc Cưới đỏ son rộn ràng, kèm bản đồ Google Maps tương tác.'],
                    ['name' => 'Chuyện tình mùa xuân & Sách ảnh bìa da lật trang 10 ảnh', 'detail' => 'Điểm nhấn đắt giá là cuốn sách ảnh cưới bìa da cho phép khách mời lật mở từng trang ảnh cưới ngay trên màn hình điện thoại.'],
                    ['name' => 'Bộ tương tác Ký tên — RSVP — VietQR trọn vẹn', 'detail' => 'Khép lại bằng sổ ký tên đậm chất thư pháp, biểu mẫu xác nhận tham dự và phong bao mừng cưới VietQR.'],
                ],
                'best_for' => 'Cặp đôi chụp ảnh cưới cổ phục Việt (Nhật Bình, Áo Tấc, Áo Dài truyền thống) hoặc tổ chức đám cưới vào dịp cuối năm – đầu xuân.',
            ],
            'hom-nay-minh-hua-nhau' => [
                'theme_palette' => 'Trắng Tinh Khôi, Đen Mực In & Điểm Nhấn Hồng Phấn',
                'opening_effect' => 'Tờ bìa mở tối giản Minimal Modern Opening Sheet mở ra cột báo giấy trắng thanh thoát kiểu tạp chí Hàn Quốc',
                'signature_parts' => [
                    ['name' => 'Trang bìa Save The Date kèm dây đèn lồng mảnh', 'detail' => 'Không gian trắng thoáng đãng làm nổi bật tối đa ảnh cưới và đường nét typography hiện đại, tối giản.'],
                    ['name' => 'Chân dung so le bất đối xứng & Trái tim hồng 3D', 'detail' => 'Thẻ ảnh Cô Dâu bên trái và Chú Rể bên phải xếp lệch nhịp nghệ thuật, nối bằng tên thư pháp và trái tim hồng 3D bóng bẩy.'],
                    ['name' => 'Sổ tay giấy xé (Torn Notebook) kể chuyện tình yêu', 'detail' => 'Câu chuyện tình yêu trình bày như trang giấy sổ tay xé cạnh tự nhiên, mang lại cảm xúc chân thành và gần gũi.'],
                    ['name' => 'Khối ngày cưới kẻ đôi Editorial & Lịch trình hiện đại', 'detail' => 'Hiển thị ngày giờ tiệc kiểu đường kẻ đôi sắc nét, lịch trình gọn gàng, album 10 ảnh, ký tên, RSVP và VietQR.'],
                ],
                'best_for' => 'Cặp đôi trẻ Gen Z – Millennials yêu phong cách tối giản Hàn Quốc (Korean Minimalist), chụp ảnh studio phông trơn hoặc ảnh phóng sự cưới tự nhiên.',
            ],
            'loi-hua-bang-nu-cuoi' => [
                'theme_palette' => 'Hồng Phấn Ngọt Ngào, Đỏ Son Vẽ Tay & Kem Sáng',
                'opening_effect' => 'Phong bì hoa xuân mở thiệp tươi tắn kết hợp nét vẽ tay đỏ son vui tươi xuyên suốt các khối nội dung',
                'signature_parts' => [
                    ['name' => 'Hero Collage 3 tầng: Khung vẽ tay đỏ & Nhân vật Chibi', 'detail' => 'Cấu trúc 3 hàng cực kỳ đáng yêu: hàng trên ảnh đôi và chữ Hỷ hoa, hàng giữa cô dâu Chibi cùng ảnh 2, hàng dưới câu trích dẫn viết tay bên chú rể Chibi.'],
                    ['name' => 'Báo tin Lễ Thành Hôn & Thông tin Hôn lễ — Tiệc cưới rõ ràng', 'detail' => 'Phân tách rành mạch hai bên gia đình, giờ làm lễ, giờ khai tiệc và nút chỉ đường Google Maps.'],
                    ['name' => 'Lịch tháng hồng phấn & Chuyện tình yêu khung vẽ tay', 'detail' => 'Tờ lịch tháng màu hồng phấn dịu mắt kết hợp khu vực chuyện tình yêu đóng khung nét vẽ tay đỏ son độc đáo.'],
                    ['name' => 'Lịch trình ngày cưới, Album ảnh & Hộp quà 3D VietQR', 'detail' => 'Hoàn thiện với lịch trình trực quan, album ảnh cưới, sổ ký tên, RSVP và hộp quà mừng cưới 3D VietQR.'],
                ],
                'best_for' => 'Cặp đôi có tính cách vui vẻ, trẻ trung, thích sự dễ thương ngọt ngào và muốn gửi đến bạn bè một tấm thiệp mang lại nụ cười ngay khi mở ra.',
            ],
            'gui-em-mot-nhanh-xuan' => [
                'theme_palette' => 'Đá Cẩm Thạch Kem Sáng, Vàng Ánh Kim & Nâu Đậm',
                'opening_effect' => 'Nền đá cẩm thạch vân vàng sang trọng điểm xuyết nhành lá vàng nghệ thuật và nút nhạc cẩm thạch nổi bật',
                'signature_parts' => [
                    ['name' => 'Hero Collage Save The Date với 2 khung ảnh nghiêng viền vàng', 'detail' => 'Hai khung ảnh cưới đặt nghiêng tự nhiên viền chỉ vàng ánh kim trên nền vân đá cẩm thạch quý phái.'],
                    ['name' => 'Thông tin tiệc cưới kèm 2 huy hiệu vàng hoàng gia', 'detail' => 'Khối Lễ Thành Hôn và Tiệc Cưới được nhấn bằng cặp huy hiệu vàng tinh xảo cùng bản đồ chỉ đường rõ ràng.'],
                    ['name' => 'Đồng hồ đếm ngược, Lịch tháng vàng & Bảng màu Dresscode', 'detail' => 'Tờ lịch tháng ánh vàng đồng điệu với bảng màu trang phục và lịch trình ngày cưới trang trọng.'],
                    ['name' => 'Album 10 ảnh Lightbox & Hộp quà 3D Cẩm Thạch Vàng VietQR', 'detail' => 'Xem ảnh toàn màn hình sắc nét, hộp quà 3D vân đá cẩm thạch mở mã VietQR động, sổ ký tên và RSVP chuẩn chỉnh.'],
                ],
                'best_for' => 'Cặp đôi tổ chức tiệc cưới tại khách sạn 4–5 sao hoặc trung tâm tiệc cưới tông trắng vàng kim, yêu vẻ đẹp cổ điển sang trọng bền vững theo thời gian.',
            ],
            'hen-cuoi-vao-mua-xuan' => [
                'theme_palette' => 'Xanh Lá Vườn Kính, Trắng Hoa Xuân & Hiệu Ứng Kính Mờ (Frosted Glass)',
                'opening_effect' => 'Vòm kính mờ (Frosted Glass Arch) ôm lấy bó hoa màu nước xanh trắng tươi mát tạo chiều sâu hiện đại',
                'signature_parts' => [
                    ['name' => 'Hero Vòm Kính Mờ & Khối báo tin Frosted Glass Panel', 'detail' => 'Ứng dụng hiệu ứng kính mờ hiện đại trên nền hoa màu nước xanh lá, giúp chữ hiển thị trong trẻo và sang trọng.'],
                    ['name' => 'Lịch tháng Vườn Xanh & Đồng hồ đếm ngược', 'detail' => 'Tờ lịch tháng đánh dấu ngày cưới bằng biểu tượng trái tim hòa quyện trong tông xanh mát mắt.'],
                    ['name' => 'Lịch trình ngày cưới với bộ biểu tượng màu nước vẽ riêng', 'detail' => 'Từng mốc đón khách, làm lễ, khai tiệc đều đi kèm minh họa màu nước dịu dàng và bảng màu Dresscode hài hòa.'],
                    ['name' => 'Album 10 ảnh, Hộp quà 3D Vườn Xanh VietQR & Sổ ký tên', 'detail' => 'Trọn bộ album ảnh Lightbox, hộp quà 3D màu xanh vườn kính mở mã VietQR động và khu vực RSVP sạch đẹp.'],
                ],
                'best_for' => 'Cặp đôi yêu thiên nhiên, tổ chức tiệc cưới ngoài trời, tiệc cưới mùa xuân hoặc bộ ảnh cưới chụp giữa vườn hoa và ánh nắng tự nhiên.',
            ],
            'mua-moi-co-chung-ta' => [
                'theme_palette' => 'Lụa Kem Ấm, Nâu Đất Hạt Dẻ & Nét Hoa Cổ Điển',
                'opening_effect' => 'Nền lụa kem ấm áp điểm họa tiết hoa nét mảnh (Line-Art Botanical Blossom) in chìm và thư pháp The Nautigal mềm mại',
                'signature_parts' => [
                    ['name' => 'Hero Hoa Nét Mảnh & Thư pháp The Nautigal bay bổng', 'detail' => 'Kết hợp hài hòa giữa nét vẽ hoa thực vật tinh giản và phông chữ thư pháp thanh mảnh mang hơi thở cổ điển.'],
                    ['name' => 'Khối báo tin in chìm hoa lụa & Bản đồ tiệc cưới', 'detail' => 'Thông tin gia đình hai họ đặt trên nền watermark hoa lụa dịu nhẹ, dễ đọc với cả người lớn tuổi.'],
                    ['name' => 'Đếm ngược & Lịch tháng nâu ấm', 'detail' => 'Sắc nâu đất trầm ấm mang lại cảm giác vững chãi, gắn kết xuyên suốt phần lịch tháng, chuyện tình yêu, Dresscode và lịch trình.'],
                    ['name' => 'Hộp quà 3D Hoa Lụa Nâu & RSVP nền nâu hạt dẻ', 'detail' => 'Hộp quà 3D đồng bộ tông nâu lụa mở mã VietQR động đi cùng sổ ký tên và biểu mẫu RSVP thuần màu thanh lịch.'],
                ],
                'best_for' => 'Cặp đôi yêu phong cách trầm ấm, cổ điển Á Đông hoặc ảnh cưới tông màu phim nâu kem (Vintage Warm Tone).',
            ],
            'mot-mua-he-ben-nhau' => [
                'theme_palette' => 'Nâu Mộc Boho Rustic, Kem Hoa Màu Nước & Vàng Gỗ',
                'opening_effect' => 'Vương miện hoa màu nước Boho mở đầu trang bìa cùng 2 khung chân dung nghiêng và dải ruy băng mềm mại',
                'signature_parts' => [
                    ['name' => 'Hero 2 khung chân dung nghiêng Cô Dâu – Chú Rể', 'detail' => 'Bố cục đối xứng sinh động: hàng Chú Rể với ảnh nghiêng bên trái, hàng Cô Dâu với ảnh nghiêng bên phải kết nối bằng dải ruy băng mộc mạc.'],
                    ['name' => 'Album 10 ảnh Coverflow 3D đặt nổi bật ở phần đầu', 'detail' => 'Đưa trải nghiệm vuốt Album Coverflow 3D kết hợp lưới ảnh thu nhỏ và Lightbox lên ngay sau phần báo hỷ để khoe trọn bộ ảnh cưới đẹp.'],
                    ['name' => 'Lịch trái tim nâu gỗ Rustic & Đồng hồ đếm ngược', 'detail' => 'Tờ lịch tháng mộc mạc đánh dấu trái tim nâu gỗ đi kèm câu chuyện tình yêu, Dresscode và lịch trình tiệc cưới.'],
                    ['name' => 'Hộp quà 3D Hoa Mộc Nâu & Bộ tương tác RSVP — Ký tên', 'detail' => 'Hộp quà Boho 3D mở mã VietQR động giúp khách ở xa dễ dàng gửi quà mừng cưới trực tiếp cho hai bạn.'],
                ],
                'best_for' => 'Cặp đôi có bộ ảnh cưới ngoại cảnh biển, đồi cỏ hoặc phong cách Boho Rustic phóng khoáng muốn khoe thật nhiều ảnh đẹp ngay từ nửa đầu thiệp.',
            ],
            'mam-trau-do-chu-duyen' => [
                'theme_palette' => 'Đỏ Sơn Mài Cung Đình, Vàng Kim Tuyến & Kem Cổ Điển',
                'opening_effect' => 'Huy chương Rồng Phượng (Long Phụng) thêu kim tuyến vàng ánh kim uy nghi ngay đầu trang bìa',
                'signature_parts' => [
                    ['name' => 'Hero Huy chương Long Phụng & Khung ảnh tròn vàng ánh kim', 'detail' => 'Biểu tượng Rồng Phượng sum vầy truyền thống bao quanh khung ảnh cưới tròn viền vàng cực kỳ trang trọng và đậm bản sắc Việt.'],
                    ['name' => 'Lịch trái tim Hoàng Gia & Lịch trình biểu tượng cung đình', 'detail' => 'Tờ lịch tháng đỏ vàng quyền quý kết hợp các mốc lịch trình ngày cưới có biểu tượng cung đình vẽ riêng.'],
                    ['name' => 'Thông tin 2 họ, Bản đồ, Dresscode & Album 10 ảnh', 'detail' => 'Phân tách rõ Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới và album 10 ảnh phóng to sắc nét.'],
                    ['name' => 'Phong bao lì xì Long Phụng Đỏ VietQR & Sổ ký tên sơn mài', 'detail' => 'Mục mừng cưới thiết kế đúng hình phong bao lì xì Long Phụng đỏ mở mã VietQR động, đi kèm khung RSVP sơn mài đỏ vàng.'],
                ],
                'best_for' => 'Gia đình đề cao nghi lễ truyền thống, lễ ăn hỏi tráp rồng phượng và muốn một tấm thiệp cưới đỏ vàng thật sang trọng, chuẩn mực để gửi họ hàng nội ngoại.',
            ],
            'mai-lan-no-ben-them' => [
                'theme_palette' => 'Xanh Ngọc Mai Lan (Jasmine Green), Trắng Tinh Khôi & Vàng Ánh Kim',
                'opening_effect' => 'Khung vòm Oval viền vàng ánh kim ôm trọn cành hoa mai lan trắng trên nền giấy màu nước dịu dàng',
                'signature_parts' => [
                    ['name' => 'Hero Vòm Oval vàng ánh kim & Hoa mai lan trắng', 'detail' => 'Tạo cảm giác thanh khiết, mềm mại và giàu chất thơ ngay từ màn hình đầu tiên với nút nhạc xanh ngọc nổi bật.'],
                    ['name' => 'Thiệp báo tin viền vàng & Lịch trái tim Mai Lan', 'detail' => 'Thông tin song thân hai họ đóng khung chỉ vàng sang trọng, tiếp nối là bản đồ tiệc cưới và tờ lịch trái tim màu vàng ấm.'],
                    ['name' => 'Lịch trình biểu tượng hoa mai lan, Dresscode & Album 10 ảnh', 'detail' => 'Mỗi mốc thời gian trong ngày cưới được minh họa tinh tế bằng bộ biểu tượng mai lan riêng đồng bộ với album ảnh Lightbox.'],
                    ['name' => 'Hộp quà 3D Mai Lan Xanh VietQR & Sổ ký tên giấy vàng ánh kim', 'detail' => 'Khép lại bằng hộp quà 3D xanh ngọc mở VietQR, sổ ký tên trên nền giấy vàng ánh kim và khung RSVP xanh ngọc thanh lịch.'],
                ],
                'best_for' => 'Cặp đôi thích vẻ đẹp thanh tao, nhẹ nhàng nhưng vẫn đủ sang trọng — cực kỳ hợp với váy cưới trắng cổ điển và tiệc cưới không gian sáng.',
            ],
            'tiec-hong-mung-duyen-moi' => [
                'theme_palette' => 'Xanh Thanh Cung Lam (Qasr Blue), Cát Vàng Hoàng Gia & Ánh Đèn Lồng',
                'opening_effect' => 'Khung vòm cung điện xanh lam rủ rèm nhung, đèn lồng vàng tỏa sáng và tòa lâu đài mái vòm cổ tích',
                'signature_parts' => [
                    ['name' => 'Hero Vòm Cung Điện, Đèn Lồng Vàng & Lâu Đài Mái Vòm', 'detail' => 'Thiết kế kiến trúc cung điện sang trọng hiếm có, biến tấm thiệp cưới thành lời mời bước vào một đêm dạ tiệc hoàng gia.'],
                    ['name' => 'Khung cột cung điện cho Thông tin Hai Họ & Báo hỷ', 'detail' => 'Đặt tên song thân và cô dâu chú rể trong khung cột đối xứng uy nghiêm, dễ nhìn và giàu tính biểu tượng.'],
                    ['name' => 'Lịch trái tim Cung Đình & Lịch trình biểu tượng Hoàng Gia', 'detail' => 'Đồng bộ hoàn toàn từ tờ lịch tháng, đồng hồ đếm ngược, câu chuyện tình yêu cho đến bảng màu Dresscode và album 10 ảnh.'],
                    ['name' => 'Phong bao mừng cưới Qasr Blue VietQR & Sổ ký tên xanh cát', 'detail' => 'Phong bao màu xanh lam hoàng gia mở mã VietQR động kết hợp khung ký tên và RSVP sắc xanh lam – vàng cát đồng nhất.'],
                ],
                'best_for' => 'Cặp đôi tổ chức tiệc cưới quy mô lớn tại sảnh ballroom sang trọng, thích gam màu xanh lam hoàng gia quyền quý và khác biệt so với tông đỏ/hồng quen thuộc.',
            ],
            'tinh-yeu-qua-tung-dong-thu' => [
                'theme_palette' => 'Giấy Báo Sài Gòn Xưa, Vàng Cũ, Xanh Gạch Bông & Đỏ Dấu Bưu Điện',
                'opening_effect' => 'Màn mở Tờ Nhật Báo Gấp Đôi có dán phiếu tên người nhận cùng hình vẽ Chợ Bến Thành, hàng me đường Duy Tân và xe Vespa cổ treo hoa cưới',
                'signature_parts' => [
                    ['name' => 'Kho hình vẽ SVG Sài Gòn Xưa & Lá me rơi nhẹ', 'detail' => 'Tích hợp sẵn bộ biểu tượng SVG độc quyền: tháp đồng hồ Chợ Bến Thành, xe Vespa cổ, cây me cổ thụ, con dấu Bưu điện Sài Gòn và hoa gạch bông 8 cánh.'],
                    ['name' => 'Bố cục Nhật Báo Tình Yêu & Bảng hiệu vẽ tay Rạp Hạnh Phúc', 'detail' => 'Trình bày thông tin cưới như một ấn phẩm báo chí hoài niệm với lịch bloc xé tay và đài radio gỗ "Đài Tình Yêu".'],
                    ['name' => 'Tem thư răng cưa Dresscode & Bưu thiếp Carte Postale', 'detail' => 'Bảng màu trang phục biến hóa thành những con tem thư răng cưa độc đáo, lời cảm ơn kết thiệp như tấm bưu thiếp xưa.'],
                    ['name' => 'Thùng thư Bưu Điện Sài Gòn mở Bưu Phiếu VietQR', 'detail' => 'Khách mời chạm vào thùng thư Bưu Điện để mở Bưu Phiếu mừng cưới VietQR — một trải nghiệm tương tác cực kỳ thú vị và khó quên.'],
                ],
                'best_for' => 'Cặp đôi yêu nét hoài cổ Retro Việt Nam, chụp ảnh cưới phong cách Sài Gòn xưa, áo dài cổ điển hoặc muốn khách mời phải trầm trồ vì độ sáng tạo độc nhất vô nhị.',
            ],
            'ban-nhac-khong-doan-ket' => [
                'theme_palette' => 'Hồng Phấn Editorial, Kem Trắng & Đen Nhung Đĩa Than',
                'opening_effect' => 'Màn mở 2 cánh cửa kính với 3 khung vòm lồng nhau kết hợp lớp bướm phấn bay nhẹ xuyên suốt trang thiệp',
                'signature_parts' => [
                    ['name' => 'Save The Date với 5 Quạt giấy xếp nan & Dải ảnh chạy dọc', 'detail' => 'Cụm 5 quạt ảnh xếp nan nghệ thuật đi kèm đồng hồ đếm ngược xếp dọc song song với dải ảnh cưới chuyển động mượt mà.'],
                    ['name' => 'Máy Đĩa Than Vinyl xoay 360° đặt giữa Cô Dâu & Chú Rể', 'detail' => 'Đĩa than "Bản Nhạc Không Đoạn Kết" quay tròn đồng bộ với bản nhạc cưới đang phát, tạo điểm nhấn cảm xúc cực mạnh.'],
                    ['name' => 'Bản đồ 2 cánh mở & Bảng màu Dresscode thắt nơ lụa', 'detail' => 'Khung bản đồ mở 2 cánh trực quan cùng dải màu trang phục thắt nơ lụa hồng phấn vô cùng duyên dáng.'],
                    ['name' => 'Hộp quà VietQR 2 cánh phát sáng & Vòm đôi Thank You', 'detail' => 'Hộp quà mừng cưới mở 2 cánh tỏa ánh sáng nhẹ hiện mã VietQR động và kết thúc bằng khung vòm đôi cảm ơn sang trọng.'],
                ],
                'best_for' => 'Cặp đôi yêu phong cách lãng mạn tinh tế pha chút nghệ thuật âm nhạc, bộ ảnh cưới váy trắng bay bổng và thích những hiệu ứng tương tác giàu cảm xúc.',
            ],
            'tieng-hat-trong-le-duong' => [
                'theme_palette' => 'Xanh Rêu Vữa Nhám Xứ Huế, Tre Trúc Vàng Kim & Hoa Trúc Đào Hồng Phấn',
                'opening_effect' => 'Cổng 2 cánh cửa xanh rêu buộc dải lụa đỏ & ấn triện vàng mở ra không gian cánh hoa trúc đào rơi nhẹ',
                'signature_parts' => [
                    ['name' => '13 biểu tượng SVG Tre Trúc & Hoa Trúc Đào vẽ riêng', 'detail' => 'Trang bìa Hero tỉ lệ 3:4 khung vòm viền kem chỉ vàng ôm chùm hoa trúc đào mang đậm hồn thơ xứ Huế.'],
                    ['name' => 'Bản Đồ Cửa Tre 2 Cánh 3D Tương Tác', 'detail' => 'Thông tin Lễ Vu Quy, Lễ Thành Hôn dẫn tới khung bản đồ thiết kế dạng cánh cửa tre 2 cánh mở 3D cực kỳ độc đáo.'],
                    ['name' => 'Chuyện tình "Trúc Đào" & Dresscode Ống Trúc Cắm Hoa', 'detail' => 'Bảng màu trang phục sáng tạo thành các ống trúc cắm hoa nghệ thuật đồng bộ với lịch tháng và đồng hồ đếm ngược.'],
                    ['name' => 'Bao lì xì sơn mài đỏ VietQR & Tranh cuộn tre ký tên HTML5', 'detail' => 'Khách mời ký tên trực tiếp lên tranh cuộn tre, treo lời chúc lên thẻ tre và gửi mừng cưới qua bao lì xì sơn mài đỏ VietQR.'],
                ],
                'best_for' => 'Cặp đôi yêu văn hóa Việt Nam sâu lắng, chụp ảnh cưới áo dài cố đô hoặc muốn một tấm thiệp nghệ thuật mang đậm chất Á Đông độc bản.',
            ],
            'hoa-nhip-doi-trai-tim' => [
                'theme_palette' => 'Kem Vàng Ánh Kim, Xanh Mặt Hồ Nhẹ & Vàng Champagne Luxury',
                'opening_effect' => 'Màn mở thiệp Mặt Hồ Gợn Sóng, Mây Cát Tường & Đôi Hạc Trắng kết hợp nút nhạc vàng kim sang trọng',
                'signature_parts' => [
                    ['name' => 'Thư mời & Nghi lễ Gia Tiên đặt ngay sau Hero Cover', 'detail' => 'Ưu tiên đưa thông tin Lễ Vu Quy, Lễ Thành Hôn và Thư mời lên vị trí dễ thấy ngay khi khách vừa mở thiệp.'],
                    ['name' => 'Đồng hồ đếm ngược Trái Tim Vàng chuyển sắc SVG', 'detail' => 'Trái tim ánh kim đổ bóng mượt mà bằng SVG Gradient đứng cạnh lịch cưới Save The Date tinh xảo.'],
                    ['name' => 'Lịch trình "Dòng Suối Thời Gian" & Dải nhắc nhớ ngày cưới', 'detail' => 'Lịch trình lễ tiệc uốn lượn mềm mại như dòng suối kết hợp dải nhắc nhớ ngày trọng đại đầy duyên dáng.'],
                    ['name' => 'Album 10 ảnh Coverflow 3D & Bao lì xì Song Hỷ VietQR', 'detail' => 'Trình diễn 10 ảnh cưới 3D Coverflow kèm Lightbox phóng to, sổ ký tên, RSVP và bao lì xì Song Hỷ mở mã VietQR.'],
                ],
                'best_for' => 'Cặp đôi tìm kiếm vẻ đẹp Á Đông sang trọng nhẹ nhàng (không quá đậm màu đỏ), hợp với tiệc cưới khách sạn ánh đèn vàng ấm áp.',
            ],
            'net-dep-cua-dinh-uoc' => [
                'theme_palette' => 'Lụa Nâu Trầm Ấm Áp, Kem Vải Thêu & Chỉ Vàng Thủ Công',
                'opening_effect' => 'Màn mở thiệp 12 dải lụa rút sợi thủ công kết hợp hiệu ứng cánh hoa rơi nhẹ và đường chỉ khâu tay tinh xảo',
                'signature_parts' => [
                    ['name' => 'Cảm hứng Khung Thêu Thủ Công & Đường chỉ khâu tay', 'detail' => 'Từng khung viền trên thiệp mô phỏng đường kim mũi chỉ thêu tay tỉ mỉ, mang lại cảm giác ấm áp và chân thành hiếm có.'],
                    ['name' => 'Bộ ba ảnh vòm (3-Photo Arched Triptych) & Các chương Chuyện tình yêu', 'detail' => 'Khoe trọn 3 bức ảnh cưới đẹp nhất trong cụm 3 khung vòm nối tiếp, dẫn vào từng chương chuyện tình yêu sâu lắng.'],
                    ['name' => 'Album 10 ảnh Coverflow 3D, Lịch trình & Bảng màu Dresscode', 'detail' => 'Sắp xếp trọn vẹn 10 phần nội dung chuẩn Mây Hẹn trên nền lụa nâu trầm quý phái, rất nịnh mắt trên điện thoại.'],
                    ['name' => 'Túi Gấm rút dây (Brocade Drawstring Pouch) mở mã VietQR', 'detail' => 'Phần mừng cưới thiết kế hình chiếc Túi Gấm truyền thống rút dây mở mã VietQR động — chi tiết thủ công đắt giá làm nên tên tuổi mẫu thiệp.'],
                ],
                'best_for' => 'Cặp đôi yêu vẻ đẹp hoài niệm, sâu lắng, chụp ảnh cưới tông nâu trầm ấm hoặc tổ chức lễ đính hôn – lễ cưới thân mật ấm cúng.',
            ],
        ];
    }
}
