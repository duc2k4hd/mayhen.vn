<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use Illuminate\Support\Facades\File;

/**
 * Knowledge & Rule Engine cho Mây Hẹn – Thiên Mệnh:
 * - Tính Tam Phương Tứ Chính, Xung Chiếu, Giáp Cung & Vô Chính Diệu cho toàn bộ 12 cung.
 * - Xác minh Cách Cục hoàn toàn bằng quy tắc kiểm chứng được (AI không được tự gán tên cách cục).
 * - Xây dựng khung đối chiếu Nhân Duyên Song Mệnh (10 chủ đề cặp đôi — KHÔNG chấm điểm % hợp tuổi hay % ly hôn).
 */
class TuViKnowledgeRuleEngine
{
    /** @var array<int, array{key: string, label: string, focus: string}> */
    public const SYNASTRY_TOPICS = [
        ['key' => 'su_thau_hieu', 'label' => 'Sự thấu hiểu', 'focus' => 'Cách hai người nhìn nhận thế giới và thấu cảm nội tâm của nhau'],
        ['key' => 'yeu_thuong', 'label' => 'Cách thể hiện yêu thương', 'focus' => 'Ngôn ngữ yêu thương tự nhiên và điều khiến đối phương thấy an tâm'],
        ['key' => 'giao_tiep', 'label' => 'Giao tiếp', 'focus' => 'Nhịp trò chuyện, phong cách trao đổi thẳng thắn hay tinh tế'],
        ['key' => 'mau_thuan', 'label' => 'Giải quyết mâu thuẫn', 'focus' => 'Cách hạ nhiệt cảm xúc và tìm điểm cân bằng khi khác quan điểm'],
        ['key' => 'tai_chinh', 'label' => 'Quan điểm tài chính', 'focus' => 'Phong cách tích lũy, chi tiêu và minh bạch ngân sách chung'],
        ['key' => 'muc_tieu', 'label' => 'Mục tiêu chung', 'focus' => 'Tầm nhìn 3–5 năm về sự nghiệp, phong cách sống và ưu tiên gia đình'],
        ['key' => 'gia_dinh', 'label' => 'Gia đình hai bên', 'focus' => 'Nghệ thuật kết nối ấm áp giữa hai gia đình nội ngoại'],
        ['key' => 'sau_hon_nhan', 'label' => 'Cuộc sống sau hôn nhân', 'focus' => 'Phân chia vai trò, nhịp sinh hoạt và nuôi dưỡng sự mới mẻ'],
        ['key' => 'to_am', 'label' => 'Xây dựng tổ ấm', 'focus' => 'Không gian sống, an cư và nề nếp ấm êm trong nhà'],
        ['key' => 'truoc_khi_cuoi', 'label' => 'Điều nên trao đổi trước khi cưới', 'focus' => 'Những câu hỏi thực tế hai bạn nên thống nhất sớm trước ngày trọng đại'],
    ];

    /** @var list<array<string, mixed>>|null */
    protected static ?array $patternsCatalog = null;

    /** @var array<string, array<string, string>>|null */
    protected static ?array $starGlossaryCache = null;

    /** @var array<string, array{group: string, element: string, nature: string, meaning: string}> */
    protected const RING_AND_LUUNIEN_GLOSSARY = [

    // =========================================================
    // 12 SAO VÒNG THÁI TUẾ
    // Thể hiện thái độ sống, tính cách, cách ứng xử,
    // các mối quan hệ xã hội và phản ứng trước hoàn cảnh.
    // =========================================================

        'Thái Tuế' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Đại diện cho sự chính trực, lòng tự trọng, khả năng tranh luận và bảo vệ quan điểm cá nhân. Khi phát huy tốt, sao này thể hiện tinh thần trách nhiệm, khả năng lãnh đạo, dám lên tiếng trước những điều sai trái và được người khác tôn trọng. Tuy nhiên, cũng dễ trở nên bảo thủ, cố chấp, thích phân định đúng sai hoặc vướng vào tranh cãi, hiểu lầm và thị phi. Cần biết lắng nghe, tôn trọng ý kiến khác biệt và lựa chọn thời điểm thích hợp để bày tỏ quan điểm.'
        ],

        'Thiếu Dương' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho ánh sáng của trí tuệ, sự thông minh, nhanh nhạy và khả năng nhìn nhận vấn đề. Sao này thiên về khả năng học hỏi nhanh, nhận biết cơ hội, tìm ra cách giải quyết khó khăn và thích nghi với môi trường mới. Trong công việc và cuộc sống, có thể phát huy khả năng sáng tạo, giao tiếp và nhận được sự giúp đỡ từ người khác. Tuy nhiên, cần tránh quá tự tin vào sự thông minh của mình, nóng vội hoặc chủ quan khi đưa ra quyết định.'
        ],

        'Tang Môn' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Mộc',
            'nature' => 'thach_thuc',
            'meaning' => 'Đại diện cho nỗi buồn, sự lo lắng, những suy nghĩ sâu sắc và các vấn đề khiến bản thân phải bận tâm. Sao này thường được liên hệ với người sống tình cảm, hay suy nghĩ về gia đình, người thân hoặc những chuyện đã qua. Mặt tích cực là khả năng đồng cảm, thấu hiểu người khác và nhìn nhận cuộc sống có chiều sâu. Mặt hạn chế là dễ suy nghĩ tiêu cực, lo xa hoặc tự tạo áp lực tinh thần. Trong Tử Vi truyền thống, sao này còn liên quan đến sự chia ly và tang chế, nhưng không có nghĩa cứ xuất hiện là sẽ có người mất.'
        ],

        'Thiếu Âm' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Thủy',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho sự dịu dàng, tinh tế, nhẫn nại và khả năng thấu hiểu cảm xúc của người khác. Sao này thiên về cách giải quyết vấn đề bằng sự mềm mỏng, khéo léo thay vì đối đầu trực tiếp. Trong các mối quan hệ, thường thể hiện sự quan tâm, biết lắng nghe, giữ gìn hòa khí và tạo cảm giác tin cậy. Tuy nhiên, nếu quá nhường nhịn hoặc dễ tin người thì có thể chịu thiệt thòi. Cần biết bảo vệ quyền lợi và đặt ra giới hạn cho bản thân.'
        ],

        'Quan Phù' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Đại diện cho lý lẽ, sự phân định đúng sai, luật lệ, giấy tờ và những vấn đề cần giải quyết bằng quy định rõ ràng. Mặt tích cực là khả năng phân tích, lập luận, bảo vệ quyền lợi và làm việc có nguyên tắc, phù hợp với những công việc cần tính chính xác hoặc hiểu biết pháp luật. Mặt hạn chế là dễ phát sinh tranh chấp, bất đồng, khiếu nại hoặc hiểu lầm liên quan đến lời nói, hợp đồng và cam kết. Cần kiểm tra kỹ giấy tờ, lưu giữ bằng chứng và tránh tranh luận khi đang nóng giận. Không có nghĩa chắc chắn sẽ vướng kiện tụng.'
        ],

        'Tử Phù' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Tượng trưng cho những chuyện buồn phiền, trở ngại, sự trì trệ hoặc cảm giác mệt mỏi khi công việc không diễn ra như mong muốn. Trong thực tế, có thể được diễn giải thành những việc nhỏ nhưng mất nhiều thời gian giải quyết, kế hoạch bị gián đoạn hoặc phải xử lý nhiều vấn đề cùng lúc. Sao này nhắc nhở cần kiên nhẫn, sắp xếp công việc theo thứ tự ưu tiên và tránh tự gây áp lực quá mức. Mặc dù tên sao có chữ Tử, nhưng không thể dựa vào sao này để kết luận về cái chết hay biến cố nghiêm trọng.'
        ],

        'Tuế Phá' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Đại diện cho sự phản kháng, bất đồng quan điểm, tinh thần không muốn bị ràng buộc và mong muốn thay đổi những điều chưa hợp lý. Mặt tích cực là khả năng tư duy độc lập, phản biện, phát hiện điểm yếu trong hệ thống và đưa ra cách làm mới. Mặt hạn chế là dễ cảm thấy bất mãn, không hài lòng với hoàn cảnh, xung đột với cấp trên hoặc người có quan điểm khác mình. Cần học cách góp ý mang tính xây dựng, thay đổi bằng hành động thực tế thay vì chỉ phản đối hoặc tranh cãi.'
        ],

        'Long Đức' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Thủy',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho lòng nhân hậu, sự bao dung, đức độ và khả năng làm dịu những mâu thuẫn trong cuộc sống. Sao này thiên về cách sống chân thành, biết giúp đỡ người khác, không quá đặt nặng chuyện hơn thua. Nhờ xây dựng được lòng tin và các mối quan hệ tốt đẹp, khi gặp khó khăn có thể nhận được sự hỗ trợ hoặc tìm được cách giải quyết nhẹ nhàng hơn. Trong Tử Vi, đây là một sao có ý nghĩa hỗ trợ giảm bớt những bất lợi, nhưng không có khả năng loại bỏ mọi khó khăn.'
        ],

        'Bạch Hổ' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Kim',
            'nature' => 'thach_thuc',
            'meaning' => 'Tượng trưng cho sự mạnh mẽ, quyết đoán, khí phách và khả năng đối mặt với hoàn cảnh khó khăn. Mặt tích cực là tinh thần dũng cảm, chịu được áp lực, dám bảo vệ quyền lợi và không dễ bỏ cuộc. Mặt hạn chế là dễ nóng tính, hành động quyết liệt quá mức, gặp xung đột hoặc tự đặt mình vào tình huống căng thẳng. Trong Tử Vi truyền thống, Bạch Hổ còn liên quan đến thương tích, máu huyết và tang chế. Tuy nhiên, không thể chỉ dựa vào sao này để dự đoán tai nạn, bệnh tật hoặc mất mát.'
        ],

        'Phúc Đức' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Thổ',
            'nature' => 'cat',
            'meaning' => 'Đại diện cho lòng tốt, tình nghĩa, sự bao dung và những giá trị tích cực được xây dựng từ cách sống có trách nhiệm với bản thân, gia đình và cộng đồng. Sao này thường được diễn giải là khả năng nhận được sự yêu quý, giúp đỡ hoặc hỗ trợ khi gặp khó khăn. Trong các mối quan hệ, đề cao sự chân thành, biết giữ chữ tín và quan tâm đến lợi ích lâu dài. Đây là sao mang ý nghĩa hỗ trợ và giảm nhẹ bất lợi, không đồng nghĩa với việc cuộc sống luôn may mắn hoặc không gặp trở ngại.'
        ],

        'Điếu Khách' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Đại diện cho khả năng giao tiếp, giao lưu xã hội, kết nối với người khác và những chuyện vui buồn trong các mối quan hệ. Mặt tích cực là khả năng trò chuyện, tạo không khí, mở rộng quan hệ và thích nghi với môi trường đông người. Mặt hạn chế là dễ nói nhiều, hứa nhiều, bị cuốn vào chuyện của người khác hoặc chi tiêu quá mức cho các cuộc vui. Trong Tử Vi truyền thống, sao này còn liên quan đến việc thăm hỏi, chia buồn và những chuyện buồn trong đời sống. Cần lựa chọn các mối quan hệ phù hợp và giữ lời nói có chừng mực.'
        ],

        'Trực Phù' => [
            'group' => 'Vòng Thái Tuế',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho những trở ngại nhỏ, sự thiệt thòi, cảm giác chưa được ghi nhận đúng mức hoặc phải bỏ ra nhiều công sức hơn dự kiến. Trong thực tế, có thể biểu hiện qua công việc bị chậm trễ, phải sửa chữa sai sót, đảm nhận việc khó hoặc nhận về kết quả chưa tương xứng với nỗ lực. Sao này nhắc nhở cần kiên trì, kiểm tra kỹ từng bước, xác định rõ trách nhiệm và quyền lợi của mình. Không nên vì những khó khăn trước mắt mà mất niềm tin hoặc vội vàng từ bỏ mục tiêu.'
        ],

        // =========================================================
        // 12 SAO VÒNG BÁC SĨ
        // Thể hiện năng lực, hành động, tài lộc, chi tiêu,
        // công việc và cách sử dụng nguồn lực trong cuộc sống.
        // =========================================================

        'Bác Sĩ' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Thủy',
            'nature' => 'cat',
            'meaning' => 'Đại diện cho trí tuệ, kiến thức, khả năng học hỏi, nghiên cứu và giải quyết vấn đề bằng chuyên môn. Sao này thiên về sự cẩn thận, ham tìm hiểu, biết phân tích thông tin và tích lũy kinh nghiệm. Trong công việc, có thể phát huy tốt ở những lĩnh vực cần kiến thức chuyên sâu như kỹ thuật, giáo dục, nghiên cứu, tư vấn hoặc y học. Tên sao Bác Sĩ không có nghĩa người sở hữu sao này chắc chắn sẽ làm bác sĩ. Ý nghĩa chính là khả năng sử dụng tri thức để tạo ra giá trị.'
        ],

        'Lực Sĩ' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho sức mạnh, nghị lực, sự bền bỉ và khả năng biến suy nghĩ thành hành động thực tế. Sao này đề cao tinh thần chịu khó, không ngại việc nặng, dám đối mặt với thử thách và kiên trì theo đuổi mục tiêu. Trong công việc, có thể phát huy tốt khi cần hành động nhanh, chịu áp lực hoặc trực tiếp xử lý vấn đề. Tuy nhiên, cần tránh quá tin vào sức lực của bản thân, làm việc quá sức hoặc sử dụng sự cứng rắn khi vấn đề cần được giải quyết bằng sự khéo léo.'
        ],

        'Thanh Long' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Thủy',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho tin vui, sự thuận lợi, cơ hội phát triển và khả năng vượt qua những trở ngại. Trong công việc và học tập, sao này thường được liên hệ với những chuyển biến tích cực như kế hoạch tiến triển, nhận được phản hồi tốt hoặc có người hỗ trợ. Trong đời sống, có thể biểu hiện qua tinh thần lạc quan, giao tiếp thuận lợi và các mối quan hệ tốt đẹp. Tuy nhiên, cơ hội chỉ thực sự có giá trị khi biết chủ động nắm bắt, chuẩn bị kỹ và hành động đúng lúc.'
        ],

        'Tiểu Hao' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Đại diện cho những khoản hao hụt nhỏ về tiền bạc, thời gian hoặc công sức. Trong cuộc sống, có thể liên quan đến chi phí ăn uống, mua sắm, đi lại, sửa chữa đồ dùng, quà tặng hoặc các khoản phát sinh thường xuyên. Tiểu Hao không hoàn toàn xấu vì việc chi tiền cho học tập, sức khỏe và nhu cầu cần thiết cũng mang lại giá trị. Tuy nhiên, nếu không kiểm soát tốt, nhiều khoản chi nhỏ có thể tích lũy thành số tiền lớn. Cần theo dõi ngân sách và phân biệt giữa nhu cầu thực sự với mua sắm theo cảm xúc.'
        ],

        'Tướng Quân' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Mộc',
            'nature' => 'cat',
            'meaning' => 'Đại diện cho quyền chỉ huy, khả năng lãnh đạo, tinh thần tiên phong và sự quyết đoán khi giải quyết công việc. Sao này thiên về việc chủ động đảm nhận trách nhiệm, tổ chức đội nhóm, đưa ra quyết định và dẫn dắt người khác hướng đến mục tiêu chung. Mặt hạn chế là dễ trở nên độc đoán, nóng tính, thích kiểm soát hoặc khó chấp nhận ý kiến trái chiều. Muốn phát huy tốt cần kết hợp bản lĩnh lãnh đạo với khả năng lắng nghe, phân công công việc và tôn trọng năng lực của người khác.'
        ],

        'Tấu Thư' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Kim',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho khả năng sử dụng ngôn ngữ, viết lách, trình bày, giao tiếp và xử lý văn bản. Sao này thiên về sự khéo léo khi truyền đạt thông tin, biết cách thuyết phục và diễn đạt những vấn đề phức tạp thành nội dung dễ hiểu. Trong công việc, có thể phát huy ở những lĩnh vực như hành chính, văn thư, giáo dục, truyền thông, kinh doanh, đàm phán hoặc sáng tạo nội dung. Cần sử dụng khả năng giao tiếp một cách chân thành, rõ ràng và tránh nói quá sự thật.'
        ],

        'Phi Liêm' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Đại diện cho tốc độ, sự nhanh nhẹn, tính linh hoạt và những thay đổi diễn ra bất ngờ. Mặt tích cực là khả năng phản ứng nhanh, xử lý tình huống và thích nghi tốt khi hoàn cảnh thay đổi. Mặt hạn chế là dễ hấp tấp, thiếu kiên nhẫn, quyết định khi chưa có đủ thông tin hoặc gặp những lời bàn tán không mong muốn. Trong công việc, cần cân bằng giữa tốc độ và độ chính xác, kiểm tra thông tin trước khi hành động để tránh những sai sót do vội vàng.'
        ],

        'Hỷ Thần' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho niềm vui, sự hài lòng, tinh thần phấn khởi và những sự kiện mang lại cảm xúc tích cực. Trong cuộc sống, có thể được liên hệ với những dịp gặp gỡ, đoàn tụ, ăn mừng, nhận tin vui hoặc hoàn thành một mục tiêu quan trọng. Sao này cũng thể hiện khả năng tạo không khí vui vẻ và xây dựng các mối quan hệ hòa thuận. Tuy nhiên, không thể chỉ dựa vào Hỷ Thần để khẳng định chắc chắn sẽ kết hôn, có con hoặc xảy ra một sự kiện vui cụ thể.'
        ],

        'Bệnh Phù' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Thổ',
            'nature' => 'thach_thuc',
            'meaning' => 'Tượng trưng cho sự suy giảm sức lực, cảm giác mệt mỏi, trì trệ hoặc những vấn đề khiến cơ thể và tinh thần cần được quan tâm. Trong thực tế, có thể xem đây là lời nhắc về việc cân bằng giữa công việc và nghỉ ngơi, duy trì thói quen sinh hoạt hợp lý và không bỏ qua những dấu hiệu bất thường của cơ thể. Sao này không có nghĩa chắc chắn sẽ mắc bệnh. Việc xác định tình trạng sức khỏe phải dựa trên triệu chứng thực tế và thăm khám y khoa, không dựa vào lá số Tử Vi.'
        ],

        'Đại Hao' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Đại diện cho sự hao tổn hoặc luân chuyển nguồn lực ở mức độ lớn, đặc biệt là tiền bạc, tài sản và công sức. Trong cuộc sống, có thể liên quan đến những khoản chi đáng kể như mua nhà, mua xe, sửa chữa, đầu tư kinh doanh, học tập hoặc giải quyết các nghĩa vụ tài chính. Đại Hao không đồng nghĩa với phá sản hay mất trắng tài sản, bởi khoản chi lớn cũng có thể tạo ra giá trị lâu dài. Điều cần chú ý là lập kế hoạch tài chính, duy trì quỹ dự phòng và tránh những quyết định vượt quá khả năng chi trả.'
        ],

        'Phục Binh' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Tượng trưng cho những yếu tố chưa được bộc lộ rõ ràng, sự cạnh tranh ngầm, hiểu lầm hoặc các vấn đề phát sinh phía sau một mối quan hệ hay thỏa thuận. Mặt tích cực là nhắc nhở khả năng quan sát, đánh giá tình huống và chuẩn bị phương án dự phòng. Mặt hạn chế là dễ gặp rắc rối khi quá tin tưởng, thiếu thông tin hoặc giao việc mà không có sự kiểm tra. Trong công việc và kinh doanh, cần minh bạch trách nhiệm, kiểm tra cam kết và bảo vệ thông tin quan trọng. Không nên vì có sao này mà nghi ngờ mọi người xung quanh.'
        ],

        'Quan Phủ' => [
            'group' => 'Vòng Bác Sĩ',
            'element' => 'Hỏa',
            'nature' => 'thach_thuc',
            'meaning' => 'Đại diện cho những vấn đề liên quan đến cơ quan quản lý, quy định, thủ tục hành chính, trách nhiệm và việc chấp hành pháp luật. Mặt tích cực là khả năng làm việc theo quy trình, hiểu rõ quyền hạn, nghĩa vụ và xử lý hồ sơ có hệ thống. Mặt hạn chế là dễ phát sinh phiền phức nếu giấy tờ thiếu sót, vi phạm quy định hoặc không thống nhất trách nhiệm giữa các bên. Cần chú ý hợp đồng, hóa đơn, chứng từ, thời hạn và các yêu cầu pháp lý. Quan Phủ thuộc vòng Bác Sĩ, khác với Quan Phù thuộc vòng Thái Tuế.'
        ],

        // =========================================================
        // 12 TRẠNG THÁI VÒNG TRÀNG SINH
        // Mô tả quá trình hình thành, phát triển, đạt đỉnh,
        // suy giảm, kết thúc và bắt đầu một chu kỳ mới.
        // Không trực tiếp quyết định tuổi thọ hay sức khỏe.
        // =========================================================

        'Tràng Sinh' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thủy',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho sự bắt đầu của một chu kỳ mới, giống như một hạt giống vừa nảy mầm và có khả năng tiếp tục phát triển. Sao này thể hiện sức sống, tiềm năng, khả năng thích nghi và sự khởi đầu thuận lợi. Khi luận về công việc, có thể liên hệ với cơ hội học hỏi, bắt đầu dự án hoặc xây dựng nền tảng phát triển lâu dài. Khi luận về các mối quan hệ, có thể thể hiện khả năng hình thành và nuôi dưỡng sự gắn kết. Tuy nhiên, có tiềm năng không đồng nghĩa với việc chắc chắn thành công nếu thiếu nỗ lực và điều kiện phù hợp.'
        ],

        'Mộc Dục' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thủy',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho giai đoạn làm mới, khám phá và thích nghi với môi trường sau khi một sự việc vừa hình thành. Sao này thường liên quan đến sự chú ý về ngoại hình, cái đẹp, cảm xúc, sở thích và nhu cầu trải nghiệm những điều mới mẻ. Mặt tích cực là sự linh hoạt, sức sáng tạo và khả năng thay đổi bản thân. Mặt hạn chế là dễ bị hấp dẫn bởi những điều mới, thiếu ổn định hoặc đưa ra quyết định dựa quá nhiều vào cảm xúc và hình thức bên ngoài. Cần xây dựng định hướng rõ ràng và kiên trì với những giá trị lâu dài.'
        ],

        'Quan Đới' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Kim',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho giai đoạn trưởng thành, bắt đầu xây dựng vị trí, hình ảnh và trách nhiệm của bản thân trong xã hội. Giống như một người đã có nền tảng kiến thức và đang chuẩn bị bước vào môi trường làm việc chính thức. Sao này thiên về sự cầu tiến, mong muốn được công nhận, phát triển năng lực và tạo dựng uy tín. Mặt hạn chế là dễ quan tâm quá mức đến danh tiếng, địa vị hoặc đánh giá của người khác. Cần chú trọng năng lực thực tế thay vì chỉ xây dựng hình ảnh bên ngoài.'
        ],

        'Lâm Quan' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Kim',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho giai đoạn năng lực đã đủ trưởng thành để phát huy trong thực tế, giống như một người đã có chuyên môn và bắt đầu đảm nhận công việc quan trọng. Sao này thể hiện sự tự chủ, kinh nghiệm, trách nhiệm và khả năng xây dựng vị trí ổn định. Trong công việc, có thể liên hệ với việc được giao thêm nhiệm vụ, phát huy chuyên môn hoặc mở rộng phạm vi hoạt động. Tuy nhiên, cần tránh tự mãn và tiếp tục học hỏi để duy trì sự phát triển bền vững.'
        ],

        'Đế Vượng' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Kim',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho giai đoạn phát triển mạnh nhất trong một chu kỳ, khi năng lực, nguồn lực hoặc sức ảnh hưởng đạt mức cao. Sao này thường được liên hệ với sự tự tin, quyết đoán, khả năng dẫn dắt và tinh thần chủ động nắm bắt cơ hội. Trong công việc, có thể thể hiện khả năng phát huy tối đa kinh nghiệm và nguồn lực hiện có. Tuy nhiên, trạng thái mạnh không đồng nghĩa với mọi việc đều tốt đẹp, bởi sự nóng vội, tham vọng hoặc tự tin quá mức cũng có thể làm tăng rủi ro. Cần biết duy trì thành quả và chuẩn bị cho những thay đổi tiếp theo.'
        ],

        'Suy' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thủy',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho giai đoạn phát triển bắt đầu chậm lại sau khi đã đạt đến mức cao nhất. Điều này giống như một công việc đã qua thời kỳ tăng trưởng mạnh và cần được đánh giá, điều chỉnh để tiếp tục duy trì. Sao Suy nhắc nhở nên chú trọng chất lượng, ổn định nguồn lực, củng cố thành quả và hạn chế mở rộng quá nhanh. Không có nghĩa chắc chắn sẽ thất bại, mất tiền hoặc suy sụp. Đây là trạng thái phù hợp với việc rà soát lại kế hoạch và lựa chọn hướng đi bền vững hơn.'
        ],

        'Bệnh' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho giai đoạn nguồn lực hoặc sức phát triển trở nên yếu hơn, cần được chăm sóc, sửa chữa và phục hồi. Khi luận về công việc, có thể liên hệ với tình trạng thiếu nguồn lực, quá tải hoặc một kế hoạch cần được điều chỉnh. Khi luận về bản thân, có thể xem là lời nhắc cần quan tâm đến sức khỏe, nghỉ ngơi và cân bằng tinh thần. Sao Bệnh là tên của một trạng thái trong chu kỳ Tràng Sinh, không phải kết luận rằng người có sao này đang hoặc sẽ mắc bệnh.'
        ],

        'Tử' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thủy',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho sự kết thúc hoặc ngừng phát triển của một giai đoạn, giống như một công việc đã hoàn thành vai trò và không còn tiếp tục theo cách cũ. Sao này thiên về sự lắng đọng, nhìn nhận lại quá khứ, buông bỏ những điều không còn phù hợp và chuẩn bị thay đổi. Trong thực tế, có thể liên hệ với việc kết thúc một dự án, thay đổi thói quen hoặc chuyển sang mục tiêu khác. Mặc dù tên sao có nghĩa là chết trong chu kỳ biểu tượng, nhưng tuyệt đối không thể dùng sao Tử để dự đoán cái chết hoặc tuổi thọ của một người.'
        ],

        'Mộ' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thổ',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho sự thu gom, cất giữ, bảo toàn và tích lũy những giá trị đã được tạo ra trong các giai đoạn trước. Trong tài chính, có thể liên hệ với việc tiết kiệm, tích lũy tài sản và quản lý nguồn lực lâu dài. Trong công việc, có thể thể hiện xu hướng lưu giữ kiến thức, kinh nghiệm và phát triển chiều sâu chuyên môn. Mặt hạn chế là dễ trở nên khép kín, giữ quá nhiều thứ hoặc ngại thay đổi. Cần biết bảo toàn những giá trị cần thiết nhưng vẫn linh hoạt trước cơ hội mới. Tên sao Mộ không đồng nghĩa với tang sự.'
        ],

        'Tuyệt' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thổ',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho giai đoạn một chu kỳ cũ đã đi đến điểm kết thúc, nguồn lực cũ không còn phát huy như trước và cần chuẩn bị cho sự hình thành mới. Có thể hiểu giống như việc phải từ bỏ một phương pháp làm việc không còn hiệu quả để tìm ra hướng đi khác. Mặt hạn chế là cảm giác bế tắc, thiếu định hướng hoặc khó tiếp tục dựa vào nền tảng cũ. Mặt tích cực là cơ hội làm lại, thay đổi tư duy và xây dựng một khởi đầu khác. Sao Tuyệt không có nghĩa tuyệt đường sống, tuyệt tự hoặc không còn tương lai.'
        ],

        'Thai' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Thổ',
            'nature' => 'trung',
            'meaning' => 'Tượng trưng cho giai đoạn hình thành ban đầu của một điều mới, khi tiềm năng đã xuất hiện nhưng chưa đủ trưởng thành để phát triển độc lập. Trong công việc, có thể liên hệ với ý tưởng mới, dự án đang chuẩn bị, kế hoạch chưa triển khai hoặc cơ hội cần thời gian nghiên cứu. Sao này nhắc nhở cần kiên nhẫn, xây dựng nền tảng và tránh nóng vội khi điều kiện chưa đầy đủ. Tên sao Thai mang ý nghĩa hình thành và nuôi dưỡng trong chu kỳ phát triển, không đồng nghĩa với việc chắc chắn mang thai hoặc có con.'
        ],

        'Dưỡng' => [
            'group' => 'Vòng Tràng Sinh',
            'element' => 'Mộc',
            'nature' => 'cat',
            'meaning' => 'Tượng trưng cho quá trình chăm sóc, nuôi dưỡng và tích lũy những điều kiện cần thiết trước khi bước vào giai đoạn phát triển mới. Giống như một hạt giống đang được cung cấp nước, dinh dưỡng và môi trường phù hợp để chuẩn bị nảy mầm. Trong công việc, có thể liên hệ với việc đào tạo, học tập, xây dựng đội ngũ, tích lũy kinh nghiệm hoặc chuẩn bị nguồn vốn. Trong các mối quan hệ, đề cao sự quan tâm và chăm sóc lâu dài. Cần kiên trì đầu tư đúng hướng thay vì mong muốn có kết quả ngay lập tức.'
        ],

        // =========================================================
        // CÁC SAO LƯU NIÊN
        // Là những sao được xác định theo năm đang xem.
        // Chỉ bổ sung ý nghĩa khi luận giải vận trình của năm đó,
        // không phải đặc điểm cố định trong suốt cuộc đời.
        // =========================================================

        'L. Thái Tuế' => [
            'group' => 'Lưu Niên',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Lưu Thái Tuế đại diện cho một trong những lĩnh vực dễ trở nên đáng chú ý trong năm đang xem. Khi sao này xuất hiện tại cung nào, những vấn đề liên quan đến lĩnh vực của cung đó có thể cần được quan tâm, giải quyết hoặc đảm nhận thêm trách nhiệm. Ví dụ, tại cung Quan Lộc có thể liên quan đến công việc, vị trí và trách nhiệm nghề nghiệp; tại cung Tài Bạch có thể liên quan đến các quyết định tài chính. Mặt tích cực là cơ hội thể hiện năng lực và quan điểm. Mặt hạn chế là áp lực, tranh luận, hiểu lầm hoặc thị phi. Đây là yếu tố tham khảo khi xem năm, không quyết định toàn bộ vận trình.'
        ],

        'L. Lộc Tồn' => [
            'group' => 'Lưu Niên',
            'element' => 'Thổ',
            'nature' => 'cat',
            'meaning' => 'Lưu Lộc Tồn tượng trưng cho cơ hội nhận được lợi ích, duy trì thu nhập, tích lũy tài sản hoặc bảo toàn những nguồn lực đang có trong năm đang xem. Trong công việc, có thể liên hệ với cơ hội cải thiện thu nhập, nhận thưởng, tìm được nguồn hỗ trợ hoặc xây dựng công việc ổn định hơn. Trong tài chính, thiên về khả năng tiết kiệm và giữ gìn thành quả thay vì làm giàu nhanh chóng. Tuy nhiên, sao này không bảo đảm chắc chắn sẽ có tiền, tăng lương hay trúng thưởng. Cần xem xét điều kiện thực tế và quản lý tài chính có kế hoạch.'
        ],

        'L. Kình Dương' => [
            'group' => 'Lưu Niên',
            'element' => 'Kim',
            'nature' => 'thach_thuc',
            'meaning' => 'Lưu Kình Dương tượng trưng cho sự cạnh tranh, áp lực, đối đầu và những vấn đề cần được xử lý quyết đoán trong năm đang xem. Mặt tích cực là thúc đẩy tinh thần chủ động, dũng cảm, dám giải quyết những việc khó và bảo vệ quyền lợi chính đáng. Mặt hạn chế là dễ xảy ra tranh cãi, xung đột, quyết định nóng vội hoặc hành động quá cứng rắn. Trong công việc, nên chú ý cách giao tiếp, đàm phán và xử lý bất đồng. Trong sinh hoạt, cần tuân thủ các nguyên tắc an toàn. Không thể dựa riêng vào sao này để dự báo tai nạn hoặc thương tích.'
        ],

        'L. Đà La' => [
            'group' => 'Lưu Niên',
            'element' => 'Kim',
            'nature' => 'thach_thuc',
            'meaning' => 'Lưu Đà La tượng trưng cho những trở ngại kéo dài, sự chậm trễ, vướng mắc hoặc các vấn đề khó giải quyết dứt điểm trong năm đang xem. Trong thực tế, có thể liên hệ với thủ tục mất nhiều thời gian, kế hoạch bị trì hoãn, công việc phải sửa đổi nhiều lần hoặc những mâu thuẫn chưa được giải quyết rõ ràng. Khác với tính chất đối đầu trực tiếp của Lưu Kình Dương, Lưu Đà La thiên về sự kéo dài và cản trở âm thầm. Cần kiên nhẫn, tìm nguyên nhân gốc rễ, chuẩn bị phương án dự phòng và chủ động giải quyết từng bước.'
        ],

        'L. Thiên Mã' => [
            'group' => 'Lưu Niên',
            'element' => 'Hỏa',
            'nature' => 'trung',
            'meaning' => 'Lưu Thiên Mã tượng trưng cho sự dịch chuyển, thay đổi, đi lại và mở rộng phạm vi hoạt động trong năm đang xem. Trong công việc, có thể liên hệ với việc đi công tác, thay đổi vị trí, chuyển môi trường làm việc, mở rộng thị trường hoặc tiếp cận những cơ hội mới. Trong cuộc sống, có thể liên quan đến du lịch, chuyển nơi ở hoặc thay đổi thói quen sinh hoạt. Mặt tích cực là khả năng thích nghi và tìm kiếm cơ hội thông qua sự chủ động. Mặt hạn chế là dễ bận rộn, thiếu ổn định hoặc tiêu tốn nhiều công sức. Không có nghĩa chắc chắn sẽ chuyển việc hoặc chuyển nhà.'
        ],

        'L. Tang Môn' => [
            'group' => 'Lưu Niên',
            'element' => 'Mộc',
            'nature' => 'thach_thuc',
            'meaning' => 'Lưu Tang Môn tượng trưng cho những vấn đề khiến bản thân dễ bận tâm, lo lắng hoặc có nhiều suy nghĩ về gia đình, người thân và các mối quan hệ trong năm đang xem. Trong thực tế, có thể liên hệ với những trách nhiệm gia đình, chuyện buồn, sự xa cách hoặc những thay đổi về cảm xúc. Sao này nhắc nhở cần dành thời gian quan tâm đến người thân, chia sẻ khó khăn và chăm sóc đời sống tinh thần. Trong Tử Vi truyền thống, Lưu Tang Môn còn được liên hệ với tang chế, nhưng sự xuất hiện của sao này không phải lời khẳng định rằng gia đình sẽ có tang.'
        ],

        'L. Bạch Hổ' => [
            'group' => 'Lưu Niên',
            'element' => 'Kim',
            'nature' => 'thach_thuc',
            'meaning' => 'Lưu Bạch Hổ tượng trưng cho những tình huống đòi hỏi sự mạnh mẽ, quyết đoán, khả năng chịu áp lực và tinh thần trách nhiệm trong năm đang xem. Trong công việc, có thể liên hệ với nhiệm vụ khó, vấn đề cần xử lý nhanh hoặc những tình huống phải bảo vệ quyền lợi của mình. Mặt hạn chế là dễ căng thẳng, nóng tính, làm việc quá sức hoặc hành động thiếu thận trọng. Trong Tử Vi truyền thống, sao này còn liên quan đến thương tích, máu huyết và tang chế. Tuy nhiên, không được dùng riêng sao Lưu Bạch Hổ để kết luận rằng năm đó sẽ xảy ra tai nạn, phẫu thuật hoặc mất mát.'
        ],

    ];

    protected const RING_GROUP_GLOSSARY = [

        'Vòng Thái Tuế' => 'Gồm 12 sao, được xác định theo địa chi của năm sinh. Vòng này chủ yếu thể hiện thái độ sống, cách suy nghĩ, cách ứng xử với người khác và phản ứng trước những thuận lợi hoặc khó khăn trong cuộc sống.',

        'Vòng Bác Sĩ' => 'Gồm 12 sao, được xác định dựa trên vị trí sao Lộc Tồn. Vòng này chủ yếu thể hiện năng lực cá nhân, cách hành động, làm việc, quản lý tiền bạc, sử dụng nguồn lực và giải quyết các vấn đề trong cuộc sống.',

        'Vòng Tràng Sinh' => 'Gồm 12 trạng thái mô phỏng một chu kỳ từ hình thành, phát triển, đạt đỉnh, suy giảm, kết thúc đến tái hình thành. Vòng này giúp tham khảo mức độ phát triển, khả năng duy trì và sự biến đổi của các lĩnh vực trên lá số. Không dùng để xác định trực tiếp tuổi thọ hoặc thời điểm sinh tử.',

        'Lưu Niên' => 'Là nhóm sao được xác định theo năm đang xem và có thể thay đổi vị trí qua từng năm. Các sao này bổ sung thông tin về những cơ hội, khó khăn hoặc lĩnh vực cần quan tâm trong năm, khác với các sao cố định được xác định từ thông tin năm, tháng, ngày và giờ sinh.',

    ];

    protected const STAR_NATURE_GLOSSARY = [

        'cat' => [
            'label' => 'Cát tinh',
            'description' => 'Mang ý nghĩa thiên về sự hỗ trợ, thuận lợi, cơ hội phát triển hoặc giảm nhẹ khó khăn. Tuy nhiên, mức độ thuận lợi còn phụ thuộc vào vị trí và sự kết hợp với các sao khác.'
        ],

        'trung' => [
            'label' => 'Trung tính',
            'description' => 'Không hoàn toàn tốt hoặc xấu. Sao này có thể mang lại thuận lợi hoặc khó khăn tùy thuộc vào hoàn cảnh, vị trí trên lá số và các sao đi cùng.'
        ],

        'thach_thuc' => [
            'label' => 'Thử thách',
            'description' => 'Thiên về những trở ngại, áp lực, biến động hoặc vấn đề cần được quan tâm. Không đồng nghĩa với tai họa hay thất bại, mà còn có thể thể hiện khả năng vượt khó và trưởng thành.'
        ],

    ];

    protected TuViCalculationEngine $engine;

    public function __construct(?TuViCalculationEngine $engine = null)
    {
        $this->engine = $engine ?? new TuViCalculationEngine;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPatternsCatalog(): array
    {
        if (self::$patternsCatalog === null) {
            $path = database_path('data/tuvi/patterns_rules.json');
            $raw = File::exists($path) ? json_decode((string) File::get($path), true) : [];
            self::$patternsCatalog = is_array($raw['patterns'] ?? null) ? $raw['patterns'] : [];
        }

        return self::$patternsCatalog;
    }

    /**
     * Làm giàu lá số bằng quan hệ Tam Phương Tứ Chính, Vô Chính Diệu và danh sách Cách Cục đã xác minh.
     *
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    public function analyzeChart(array $chart): array
    {
        if (($chart['status'] ?? '') === 'partial_no_birth_hour' || empty($chart['palaces_by_branch'])) {
            return [
                'relations_by_palace' => [],
                'verified_patterns' => [],
                'palace_digests' => [],
            ];
        }

        $byBranch = $chart['palaces_by_branch'];
        $palacesMeta = $this->engine->getPalacesMeta();
        $majorMeta = $this->engine->getMajorStarsMeta();

        $relationsByPalace = [];
        $palaceDigests = [];

        foreach ($byBranch as $p) {
            $b = (int) $p['branch_index'];
            $triadA = ($b + 4) % 12;
            $triadB = ($b + 8) % 12;
            $opposite = ($b + 6) % 12;
            $prevAdj = ($b + 11) % 12;
            $nextAdj = ($b + 1) % 12;

            $tptcBranches = [$b, $triadA, $triadB, $opposite];
            $tptcMajorCodes = [];
            $tptcAllCodes = [];
            foreach ($tptcBranches as $tb) {
                foreach ($byBranch[$tb]['major_stars'] as $ms) {
                    $tptcMajorCodes[] = $ms['code'];
                    $tptcAllCodes[] = $ms['code'];
                }
                foreach ($byBranch[$tb]['aux_stars'] as $as) {
                    $tptcAllCodes[] = $as['code'];
                }
                foreach ($byBranch[$tb]['tu_hoa'] as $th) {
                    $tptcAllCodes[] = $th['code'];
                }
            }

            $borrowedMajors = $p['is_vo_chinh_dieu'] ? $byBranch[$opposite]['major_stars'] : [];

            $relationsByPalace[$p['code']] = [
                'branch_index' => $b,
                'triad_branches' => [$triadA, $triadB],
                'triad_palace_codes' => [$byBranch[$triadA]['code'], $byBranch[$triadB]['code']],
                'opposite_branch' => $opposite,
                'opposite_palace_code' => $byBranch[$opposite]['code'],
                'adjacent_branches' => [$prevAdj, $nextAdj],
                'tptc_major_codes' => array_values(array_unique($tptcMajorCodes)),
                'tptc_all_codes' => array_values(array_unique($tptcAllCodes)),
                'borrowed_majors_if_vcd' => $borrowedMajors,
            ];

            $pMeta = $palacesMeta[$p['code']] ?? [];
            $focusMajors = ! empty($p['major_stars']) ? $p['major_stars'] : $borrowedMajors;
            $leadStar = $focusMajors[0] ?? null;
            $leadMeta = $leadStar ? ($majorMeta[$leadStar['code']] ?? null) : null;

            $headlineStars = ! empty($p['major_stars'])
                ? implode(' • ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $p['major_stars']))
                : 'Vô Chính Diệu (chiếu từ '.$byBranch[$opposite]['name'].': '.implode(', ', array_map(fn (array $s): string => $s['name'], $borrowedMajors)).')';

            $palaceDigests[$p['code']] = [
                'palace_code' => $p['code'],
                'palace_name' => $p['name'],
                'hanzi' => $p['hanzi'],
                'branch_name' => $p['branch_name'],
                'is_than' => $p['is_than'],
                'is_vo_chinh_dieu' => $p['is_vo_chinh_dieu'],
                'stars_headline' => $headlineStars,
                'classical_meaning' => (string) ($pMeta['classical_meaning'] ?? ''),
                'modern_meaning' => (string) ($pMeta['modern_meaning'] ?? ''),
                'lead_star_insight' => $leadMeta ? (string) $leadMeta['core_meaning'] : (string) ($pMeta['vcd_note'] ?? ''),
                'strength_hint' => $leadMeta ? (string) $leadMeta['strengths'] : 'Linh hoạt thích ứng, biết tận dụng nguồn lực xung chiếu.',
                'watch_out_hint' => $leadMeta ? (string) $leadMeta['watch_outs'] : 'Cần xác định trọng tâm rõ ràng thay vì phân tán theo hoàn cảnh.',
                'common_misconception' => (string) ($pMeta['common_misconception'] ?? ''),
                'vcd_note' => $p['is_vo_chinh_dieu'] ? (string) ($pMeta['vcd_note'] ?? '') : null,
            ];
        }

        $verifiedPatterns = $this->evaluatePatterns($chart, $relationsByPalace);
        $auxMeta = $this->engine->getAuxiliaryStarsMeta();
        $palaceDeepReports = $this->buildPalaceDeepReports($chart, $relationsByPalace, $palaceDigests, $majorMeta, $auxMeta);
        $careerOrientation = $this->buildCareerOrientation($chart, $relationsByPalace, $majorMeta);
        $thienBanOverview = $this->buildThienBanOverview($chart);

        return [
            'relations_by_palace' => $relationsByPalace,
            'verified_patterns' => $verifiedPatterns,
            'palace_digests' => $palaceDigests,
            'palace_deep_reports' => $palaceDeepReports,
            'career_orientation' => $careerOrientation,
            'thien_ban_overview' => $thienBanOverview,
            'star_glossary' => $this->getStarGlossary(),
        ];
    }

    /**
     * Từ điển giải thích toàn bộ Chính tinh, Phụ tinh, Tứ Hóa, Vòng sao & Lưu niên bằng tiếng Việt dễ hiểu.
     *
     * @return array<string, array{
     *     name: string,
     *     hanzi: string,
     *     group_label: string,
     *     element: string,
     *     nature: string,
     *     meaning: string,
     *     advice: string
     * }>
     */
    public function getStarGlossary(): array
    {
        if (self::$starGlossaryCache !== null) {
            return self::$starGlossaryCache;
        }

        $glossary = [];
        $groupLabels = [
            'luc_cat' => 'Lục Cát Tinh (Sao trợ lực)',
            'tai_cat' => 'Tài Cát Tinh',
            'dong_tinh' => 'Động Tinh (Dịch chuyển)',
            'luc_sat' => 'Lục Sát Tinh (Rèn bản lĩnh)',
            'tu_hoa' => 'Tứ Hóa',
            'nhan_duyen' => 'Sao Nhân Duyên',
            'tinh_cam' => 'Sao Độc Lập Tình Cảm',
            'ky_luat' => 'Sao Kỷ Luật',
            'phuc_thien' => 'Phúc Thiện Tinh',
            'phu_tro' => 'Phụ Trợ',
            'dac_biet' => 'Không Vong (Tuần / Triệt)',
        ];

        foreach ($this->engine->getMajorStarsMeta() as $m) {
            $name = (string) ($m['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $glossary[$name] = [
                'name' => $name,
                'hanzi' => (string) ($m['hanzi'] ?? ''),
                'group_label' => 'Chính Tinh • '.(string) ($m['archetype'] ?? ''),
                'element' => (string) ($m['element'] ?? ''),
                'nature' => 'chinh',
                'meaning' => (string) ($m['core_meaning'] ?? ''),
                'advice' => 'Phát huy: '.(string) ($m['strengths'] ?? '').' | Lưu tâm: '.(string) ($m['watch_outs'] ?? ''),
            ];
        }

        foreach ($this->engine->getAuxiliaryStarsMeta() as $a) {
            $name = (string) ($a['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $groupCode = (string) ($a['group'] ?? '');
            $glossary[$name] = [
                'name' => $name,
                'hanzi' => (string) ($a['hanzi'] ?? ''),
                'group_label' => $groupLabels[$groupCode] ?? 'Phụ Tinh',
                'element' => (string) ($a['element'] ?? ''),
                'nature' => (string) ($a['nature'] ?? 'trung'),
                'meaning' => (string) ($a['meaning'] ?? ''),
                'advice' => match ((string) ($a['nature'] ?? 'trung')) {
                    'cat' => 'Đây là sao cát tính trợ lực tích cực cho cung mà sao đóng vào.',
                    'thach_thuc' => 'Đây là sao thử thách — không phải xấu tuyệt đối mà là điểm nhắc rèn sự điềm tĩnh và có phương án dự phòng.',
                    default => 'Sao mang tính trung hòa, tính chất tốt hay thử thách phụ thuộc vào Chính tinh và Tam Hợp đi cùng.',
                },
            ];
        }

        foreach (self::RING_AND_LUUNIEN_GLOSSARY as $name => $meta) {
            $glossary[$name] = [
                'name' => $name,
                'hanzi' => '',
                'group_label' => $meta['group'],
                'element' => $meta['element'],
                'nature' => $meta['nature'],
                'meaning' => $meta['meaning'],
                'advice' => match ($meta['nature']) {
                    'cat' => 'Mang năng lượng hỗ trợ và cân bằng nhẹ nhàng cho chủ đề của cung này.',
                    'thach_thuc' => 'Gợi ý bạn chủ động điều tiết cảm xúc, giữ nguyên tắc rõ ràng ở lĩnh vực của cung này.',
                    default => 'Phản ánh nhịp vận động tự nhiên trong cung hoặc trong năm xem hạn.',
                },
            ];
        }

        // Bổ sung khóa rút gọn TUẦN / TRIỆT khớp nhãn trên bảng
        if (isset($glossary['Tuần Không'])) {
            $glossary['TUẦN'] = $glossary['Tuần Không'];
        }
        if (isset($glossary['Triệt Lộ'])) {
            $glossary['TRIỆT'] = $glossary['Triệt Lộ'];
        }

        self::$starGlossaryCache = $glossary;

        return self::$starGlossaryCache;
    }

    /**
     * Tìm các ngôi sao được nhắc tên trong câu hỏi của người dùng để phục vụ tra cứu & fallback tức thời.
     *
     * @return list<array<string, string>>
     */
    public function lookupStarsInText(string $text): array
    {
        $normText = mb_strtolower($text, 'UTF-8');
        $found = [];
        foreach ($this->getStarGlossary() as $name => $entry) {
            if (in_array($name, ['TUẦN', 'TRIỆT'], true)) {
                continue;
            }
            $nameLower = mb_strtolower($name, 'UTF-8');
            if (mb_strlen($nameLower, 'UTF-8') >= 3 && str_contains($normText, $nameLower)) {
                $found[] = $entry;
            }
        }

        return array_slice($found, 0, 4);
    }

    /**
     * Xây dựng báo cáo luận giải chi tiết & chỉ số cân bằng minh bạch cho đủ 12 cung.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @param  array<string, array<string, mixed>>  $digests
     * @param  array<string, array<string, mixed>>  $majorMeta
     * @param  array<string, array<string, mixed>>  $auxMeta
     * @return list<array<string, mixed>>
     */
    protected function buildPalaceDeepReports(
        array $chart,
        array $relations,
        array $digests,
        array $majorMeta,
        array $auxMeta
    ): array {
        $byBranch = $chart['palaces_by_branch'] ?? [];
        $byCode = $chart['palaces_by_code'] ?? [];
        $groupMap = [
            'menh' => 'core', 'quan_loc' => 'core', 'tai_bach' => 'core', 'thien_di' => 'core',
            'phu_the' => 'family', 'tu_tuc' => 'family', 'phu_mau' => 'family', 'huynh_de' => 'family',
            'phuc_duc' => 'foundation', 'dien_trach' => 'foundation', 'tat_ach' => 'foundation', 'no_boc' => 'foundation',
        ];
        $order = ['menh', 'quan_loc', 'tai_bach', 'phu_the', 'thien_di', 'phuc_duc', 'dien_trach', 'phu_mau', 'huynh_de', 'tu_tuc', 'tat_ach', 'no_boc'];
        $brightnessWeight = ['M' => 14, 'V' => 10, 'Đ' => 6, 'B' => 2, 'H' => -7];

        $reports = [];
        foreach ($order as $code) {
            $p = $byCode[$code] ?? null;
            if (! $p) {
                continue;
            }
            $rel = $relations[$code] ?? [];
            $digest = $digests[$code] ?? [];

            // 1. Tính điểm cân bằng tất định minh bạch căn cứ sao (35..95)
            $score = 62.0;
            $positivePoints = [];
            $cautionPoints = [];

            if (! empty($p['major_stars'])) {
                foreach ($p['major_stars'] as $ms) {
                    $b = (string) ($ms['brightness'] ?? 'B');
                    $score += $brightnessWeight[$b] ?? 0;
                    $mInfo = $majorMeta[$ms['code']] ?? [];
                    $sTitle = $ms['name'].' ('.$ms['brightness_label'].')';
                    if (in_array($b, ['M', 'V', 'Đ'], true)) {
                        $positivePoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['strengths'] ?? $mInfo['core_meaning'] ?? 'Chính tinh đắc cách tạo nền lực rõ ràng cho cung này.'),
                        ];
                    } elseif ($b === 'H') {
                        $cautionPoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['watch_outs'] ?? 'Chính tinh ở thế Hãm địa đòi hỏi sự kiên trì, điềm tĩnh và rèn kỷ luật thực tế.'),
                        ];
                    } else {
                        $positivePoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['core_meaning'] ?? 'Chính tinh ở thế Bình hòa, phát huy tốt khi gặp cát tinh trợ lực.'),
                        ];
                    }
                }
            } else {
                $borrowed = $rel['borrowed_majors_if_vcd'] ?? [];
                foreach ($borrowed as $bm) {
                    $score += (($brightnessWeight[$bm['brightness'] ?? 'B'] ?? 0) * 0.65);
                }
                $positivePoints[] = [
                    'star' => 'Vô Chính Diệu (Mượn chính tinh xung chiếu)',
                    'text' => (string) ($digest['vcd_note'] ?: 'Cung Vô Chính Diệu giàu khả năng thích nghi, tiếp nhận năng lượng linh hoạt từ cung xung chiếu và Tam Hợp.'),
                ];
            }

            // Tứ Hóa tại cung
            foreach ($p['tu_hoa'] ?? [] as $th) {
                $thCode = (string) ($th['code'] ?? '');
                $thMeta = $auxMeta[$thCode] ?? [];
                if ($thCode === 'hoa_ky') {
                    $score -= 6;
                    $cautionPoints[] = [
                        'star' => $th['name'].' đi cùng '.$th['attached_star_name'],
                        'text' => (string) ($thMeta['meaning'] ?? 'Cần minh bạch giao tiếp, tránh cầu toàn quá mức hoặc tự giữ bận tâm trong lòng.'),
                    ];
                } else {
                    $score += 7;
                    $positivePoints[] = [
                        'star' => $th['name'].' đi cùng '.$th['attached_star_name'],
                        'text' => (string) ($thMeta['meaning'] ?? 'Tứ Hóa cát tinh gia tăng thuận lợi, uy tín và nguồn lực thực tế.'),
                    ];
                }
            }

            // Phụ tinh Cát / Sát tại cung
            foreach ($p['aux_stars'] ?? [] as $aux) {
                $aMeta = $auxMeta[$aux['code']] ?? [];
                $nature = (string) ($aux['nature'] ?? 'trung');
                if ($nature === 'cat') {
                    $score += ($aux['group'] ?? '') === 'luc_cat' ? 4.5 : 2.5;
                    if (count($positivePoints) < 5) {
                        $positivePoints[] = [
                            'star' => $aux['name'],
                            'text' => (string) ($aMeta['meaning'] ?? 'Cát tinh bổ trợ thuận lợi và tạo điểm tựa quý nhân.'),
                        ];
                    }
                } elseif ($nature === 'thach_thuc') {
                    $score -= 5.0;
                    if (count($cautionPoints) < 4) {
                        $cautionPoints[] = [
                            'star' => $aux['name'],
                            'text' => (string) ($aMeta['meaning'] ?? 'Sao mang tính thử thách, nhắc nhở rèn bản lĩnh và giữ sự tỉnh táo trước áp lực.'),
                        ];
                    }
                }
            }

            // Tuần / Triệt điều tiết biên độ
            if (! empty($p['has_tuan'])) {
                $score = 60 + ($score - 60) * 0.78;
                $cautionPoints[] = [
                    'star' => 'Tuần Không',
                    'text' => 'Đóng vai trò vùng chuyển nhịp: làm dịu bớt tính cực đoan của sát tinh nhưng cũng khiến thành quả đến chậm chắc hơn ở giai đoạn đầu.',
                ];
            }
            if (! empty($p['has_triet'])) {
                $score = 60 + ($score - 60) * 0.76;
                $cautionPoints[] = [
                    'star' => 'Triệt Lộ',
                    'text' => 'Tạo bước ngoặt tự lập rõ rệt trong chặng đầu đời; sau tuổi 30 ảnh hưởng dần thoáng mở khi bạn đã vững nền tảng.',
                ];
            }

            $finalScore = (int) max(38, min(94, round($score)));
            if ($finalScore >= 80) {
                $tierCode = 'excellent';
                $tierLabel = 'Rất Vượng';
            } elseif ($finalScore >= 65) {
                $tierCode = 'good';
                $tierLabel = 'Khá Tốt';
            } elseif ($finalScore >= 52) {
                $tierCode = 'balanced';
                $tierLabel = 'Bình Hòa';
            } else {
                $tierCode = 'watch';
                $tierLabel = 'Cần Lưu Tâm';
            }

            $triadNames = array_map(
                fn (int $tb): string => $byBranch[$tb]['name'] ?? '',
                $rel['triad_branches'] ?? []
            );
            $oppName = isset($rel['opposite_branch']) ? ($byBranch[$rel['opposite_branch']]['name'] ?? '') : '';

            if ($cautionPoints === []) {
                $cautionPoints[] = [
                    'star' => 'Nhịp cân bằng ổn định',
                    'text' => 'Cung ít chịu xung lực sát tinh mạnh — vẫn nên chủ động đặt mục tiêu rõ ràng để tránh sự an nhàn làm chậm nhịp phát triển.',
                ];
            }

            $reports[] = [
                'code' => $code,
                'group' => $groupMap[$code] ?? 'foundation',
                'name' => $p['name'],
                'short_name' => $p['short_name'],
                'hanzi' => $p['hanzi'],
                'can_chi' => $p['can_chi'],
                'dai_han' => ($p['dai_han_start'] ?? 0).'–'.($p['dai_han_end'] ?? 0).' tuổi',
                'is_than' => (bool) ($p['is_than'] ?? false),
                'is_lai_nhan' => (bool) ($p['is_lai_nhan'] ?? false),
                'is_current_tieu_han' => (bool) ($p['is_current_tieu_han'] ?? false),
                'is_current_luu_nien' => (bool) ($p['is_current_luu_nien'] ?? false),
                'score' => $finalScore,
                'tier_code' => $tierCode,
                'tier_label' => $tierLabel,
                'stars_headline' => (string) ($digest['stars_headline'] ?? ''),
                'modern_meaning' => (string) ($digest['modern_meaning'] ?? ''),
                'strength_hint' => (string) ($digest['strength_hint'] ?? ''),
                'watch_out_hint' => (string) ($digest['watch_out_hint'] ?? ''),
                'tptc_summary' => 'Tam hợp: '.implode(' & ', array_filter($triadNames)).($oppName !== '' ? ' • Xung chiếu: '.$oppName : ''),
                'rings_summary' => 'Thái Tuế: '.($p['ring_thai_tue'] ?? '').' • Bác Sĩ: '.($p['ring_bac_si'] ?? '').' • Tràng Sinh: '.($p['ring_trang_sinh'] ?? ''),
                'positive_points' => $positivePoints,
                'caution_points' => $cautionPoints,
            ];
        }

        return $reports;
    }

    /**
     * Suy luận thiên hướng nghề nghiệp thực tế từ Tam Hợp Mệnh – Tài – Quan.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @param  array<string, array<string, mixed>>  $majorMeta
     * @return array{headline: string, summary: string, roles: list<string>}
     */
    protected function buildCareerOrientation(array $chart, array $relations, array $majorMeta): array
    {
        $quan = $chart['palaces_by_code']['quan_loc'] ?? null;
        $menh = $chart['palaces_by_code']['menh'] ?? null;
        $tai = $chart['palaces_by_code']['tai_bach'] ?? null;

        $leadStars = array_merge(
            $quan['major_stars'] ?? [],
            $menh['major_stars'] ?? [],
            $tai['major_stars'] ?? []
        );
        $codes = array_values(array_unique(array_column($leadStars, 'code')));

        $roleMap = [
            'tu_vi' => ['Quản trị cấp cao & Điều hành', 'Hoạch định chiến lược doanh nghiệp'],
            'thien_phu' => ['Tài chính, Ngân hàng & Quản trị tài sản', 'Điều hành hệ thống quy mô lớn'],
            'thai_duong' => ['Truyền thông, Ngoại giao & Cộng đồng', 'Giáo dục, Đào tạo & Dẫn dắt đội ngũ'],
            'vu_khuc' => ['Tài chính, Đầu tư & Kinh doanh thực chiến', 'Quản trị dự án & Tối ưu hiệu suất'],
            'thien_co' => ['Công nghệ, Phân tích hệ thống & Tư vấn', 'Nghiên cứu chiến lược & Thiết kế giải pháp'],
            'thien_dong' => ['Sáng tạo dịch vụ, Du lịch & Nghệ thuật', 'Chăm sóc khách hàng & Văn hóa tổ chức'],
            'liem_trinh' => ['Pháp lý, Quản trị rủi ro & Kỷ luật vận hành', 'Thiết kế thẩm mỹ & Kinh doanh sáng tạo'],
            'thai_am' => ['Bất động sản, Kiến trúc & Quản trị quỹ', 'Tâm lý, Biên tập & Nghệ thuật tinh tế'],
            'tham_lang' => ['Marketing, Đối ngoại & Phát triển thị trường', 'Ẩm thực, Thời trang & Trải nghiệm khách hàng'],
            'cu_mon' => ['Tư vấn chuyên môn, Luật & Đàm phán', 'Giảng dạy, Phân tích & Truyền thông nội dung'],
            'thien_tuong' => ['Điều phối nhân sự, Cố vấn & Dịch vụ cao cấp', 'Y tế, Giáo dục & Quản trị vận hành'],
            'thien_luong' => ['Giáo dục, Cố vấn chiến lược & Kiểm toán', 'Y tế, Công tác xã hội & Chuyên môn chuẩn mực'],
            'that_sat' => ['Khởi nghiệp, Tái cấu trúc & Mở thị trường mới', 'Kỹ thuật, Công nghiệp & Quản lý dự án tốc độ cao'],
            'pha_quan' => ['Đổi mới sản phẩm, Sáng tạo & Chuyển đổi số', 'Kinh doanh đột phá & Xây dựng mô hình mới'],
        ];

        $roles = [];
        foreach (array_slice($codes, 0, 4) as $c) {
            foreach ($roleMap[$c] ?? [] as $r) {
                $roles[] = $r;
            }
        }
        $roles = array_values(array_unique($roles));
        if ($roles === []) {
            $roles = ['Cố vấn & Điều phối đa nhiệm', 'Kinh doanh dịch vụ linh hoạt', 'Quản trị vận hành & Phát triển đội nhóm'];
        }

        $primaryStarName = $quan['major_stars'][0]['name'] ?? ($menh['major_stars'][0]['name'] ?? 'Vô Chính Diệu');
        $primaryMeta = $majorMeta[$quan['major_stars'][0]['code'] ?? ($menh['major_stars'][0]['code'] ?? '')] ?? null;

        return [
            'headline' => 'Thiên hướng phát huy mạnh theo trục Mệnh – Tài – Quan ('.$primaryStarName.')',
            'summary' => $primaryMeta
                ? (string) ($primaryMeta['career_style'] ?? $primaryMeta['strengths'] ?? 'Trục Mệnh – Tài – Quan cho thấy bạn đạt hiệu quả cao nhất khi chọn công việc phát huy đúng thế mạnh chủ đạo và có lộ trình tăng trưởng minh bạch.')
                : 'Trục Mệnh – Tài – Quan của bạn giàu độ linh hoạt — phù hợp các vai trò kết nối, cố vấn hoặc điều phối đa nguồn lực.',
            'roles' => array_slice($roles, 0, 6),
        ];
    }

    /**
     * Tổng hợp 6 trụ Thiên Bàn chuẩn Nam Phái (có fallback cho lá số cũ lưu trước bản cập nhật).
     *
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    protected function buildThienBanOverview(array $chart): array
    {
        $summary = $chart['summary'] ?? [];
        $birth = $chart['birth'] ?? [];
        $yearBranch = (int) ($birth['pillars']['year']['branch_index'] ?? 0);
        $branches = VietnameseCalendarService::BRANCHES;

        $zodiacHarmony = $summary['zodiac_harmony'] ?? [
            'tam_hop' => [$branches[($yearBranch + 4) % 12], $branches[($yearBranch + 8) % 12]],
            'luc_hop' => $branches[[0 => 1, 1 => 0, 2 => 11, 11 => 2, 3 => 10, 10 => 3, 4 => 9, 9 => 4, 5 => 8, 8 => 5, 6 => 7, 7 => 6][$yearBranch] ?? 0],
            'tu_hanh_xung' => [$branches[($yearBranch + 3) % 12], $branches[($yearBranch + 6) % 12], $branches[($yearBranch + 9) % 12]],
        ];

        return [
            'nap_am' => ($summary['nap_am']['name'] ?? '').' (Hành '.($summary['nap_am']['element'] ?? '').')',
            'cuc' => $summary['cuc']['name'] ?? '',
            'cuc_menh_relation' => $summary['cuc_menh_relation'] ?? 'Mệnh Cục tương phối hài hòa',
            'am_duong_gender' => $chart['yin_yang_gender_label'] ?? '',
            'am_duong_ly' => $summary['am_duong_ly'] ?? 'Âm Dương Thuận Lý',
            'chu_menh' => $summary['chu_menh'] ?? '',
            'chu_than' => $summary['chu_than'] ?? '',
            'than_palace_name' => $summary['than_palace_name'] ?? '',
            'lai_nhan_palace_name' => $summary['lai_nhan_palace_name'] ?? ($chart['palaces_by_code']['menh']['name'] ?? 'Cung Mệnh'),
            'can_luong' => $summary['can_luong'] ?? null,
            'zodiac_harmony' => $zodiacHarmony,
        ];
    }

    /**
     * So sánh có căn cứ giữa 2 lá số độc lập cho Nhân Duyên Song Mệnh (KHÔNG chấm % hợp tuổi/ly hôn).
     *
     * @param  array<string, mixed>  $chartA
     * @param  array<string, mixed>  $chartB
     * @return array<string, mixed>
     */
    public function buildSynastryBasis(array $chartA, array $chartB): array
    {
        $elA = (string) ($chartA['birth']['nap_am']['element'] ?? 'Thổ');
        $elB = (string) ($chartB['birth']['nap_am']['element'] ?? 'Thổ');
        $elementDynamic = $this->describeElementDynamic($elA, $elB);

        $menhA = $chartA['palaces_by_code']['menh'] ?? null;
        $menhB = $chartB['palaces_by_code']['menh'] ?? null;
        $phuTheA = $chartA['palaces_by_code']['phu_the'] ?? null;
        $phuTheB = $chartB['palaces_by_code']['phu_the'] ?? null;
        $dienA = $chartA['palaces_by_code']['dien_trach'] ?? null;
        $dienB = $chartB['palaces_by_code']['dien_trach'] ?? null;
        $phucA = $chartA['palaces_by_code']['phuc_duc'] ?? null;
        $phucB = $chartB['palaces_by_code']['phuc_duc'] ?? null;

        $starsToStr = static fn (?array $p): string => $p && ! empty($p['major_stars'])
            ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $p['major_stars']))
            : ($p ? 'Vô Chính Diệu' : 'Thiếu giờ sinh');

        return [
            'provenance' => 'mayhen_modern_guide',
            'disclaimer' => 'Nhân Duyên Song Mệnh là khung tư vấn thấu hiểu mối quan hệ của Mây Hẹn dựa trên đối chiếu hai lá số độc lập — không phải điểm số hợp tuổi hay lời tiên đoán hôn nhân.',
            'person_a' => [
                'nickname' => $chartA['nickname'] ?? 'Người thứ nhất',
                'nap_am' => $chartA['birth']['nap_am']['name'] ?? '',
                'element' => $elA,
                'menh_stars' => $starsToStr($menhA),
                'phu_the_stars' => $starsToStr($phuTheA),
                'dien_trach_stars' => $starsToStr($dienA),
                'phuc_duc_stars' => $starsToStr($phucA),
            ],
            'person_b' => [
                'nickname' => $chartB['nickname'] ?? 'Người thứ hai',
                'nap_am' => $chartB['birth']['nap_am']['name'] ?? '',
                'element' => $elB,
                'menh_stars' => $starsToStr($menhB),
                'phu_the_stars' => $starsToStr($phuTheB),
                'dien_trach_stars' => $starsToStr($dienB),
                'phuc_duc_stars' => $starsToStr($phucB),
            ],
            'element_dynamic' => $elementDynamic,
            'topics' => self::SYNASTRY_TOPICS,
        ];
    }

    /**
     * Đánh giá Cách Cục hoàn toàn tất định theo `patterns_rules.json`.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @return list<array<string, mixed>>
     */
    protected function evaluatePatterns(array $chart, array $relations): array
    {
        $catalog = $this->getPatternsCatalog();
        $menhPalace = $chart['palaces_by_code']['menh'] ?? null;
        $menhRel = $relations['menh'] ?? null;
        $phuTheRel = $relations['phu_the'] ?? null;

        if (! $menhPalace || ! $menhRel) {
            return [];
        }

        $byBranch = $chart['palaces_by_branch'];
        $triadBranches = array_merge([$menhRel['branch_index']], $menhRel['triad_branches']);
        $menhTriadMajors = [];
        foreach ($triadBranches as $tb) {
            foreach ($byBranch[$tb]['major_stars'] as $ms) {
                $menhTriadMajors[] = $ms['code'];
            }
        }
        $menhTriadMajors = array_values(array_unique($menhTriadMajors));

        $matched = [];
        foreach ($catalog as $rule) {
            $type = (string) ($rule['type'] ?? '');
            $isMatch = false;
            $evidence = '';

            if ($type === 'tam_hop_menh') {
                $req = $rule['required_stars_in_menh_triad'] ?? [];
                $minMatch = (int) ($rule['min_match'] ?? count($req));
                $hit = array_values(array_intersect($req, $menhTriadMajors));
                if (count($hit) >= $minMatch) {
                    $isMatch = true;
                    $evidence = 'Tam hợp Mệnh–Tài–Quan hội đủ '.count($hit).' chính tinh trong bộ: '.implode(', ', $hit);
                }
            } elseif ($type === 'menh_tptc') {
                $req = $rule['required_stars_in_menh_tptc'] ?? [];
                $hit = array_values(array_intersect($req, $menhRel['tptc_all_codes'] ?? []));
                if (count($hit) === count($req) && $req !== []) {
                    $isMatch = true;
                    $evidence = 'Tam Phương Tứ Chính cung Mệnh hội đủ: '.implode(', ', $hit);
                }
            } elseif ($type === 'phu_the_tptc' && $phuTheRel) {
                $req = $rule['required_stars_in_phu_the_tptc'] ?? [];
                $hit = array_values(array_intersect($req, $phuTheRel['tptc_all_codes'] ?? []));
                if (count($hit) === count($req) && $req !== []) {
                    $isMatch = true;
                    $evidence = 'Tam Phương Tứ Chính cung Phu Thê hội: '.implode(', ', $hit);
                }
            } elseif ($type === 'special_vcd' && ! empty($menhPalace['is_vo_chinh_dieu'])) {
                $isMatch = true;
                $evidence = 'Cung Mệnh tại '.$menhPalace['branch_name'].' không có chính tinh tọa thủ (mượn ánh sáng cung Thiên Di).';
            }

            if ($isMatch) {
                $matched[] = [
                    'code' => (string) $rule['code'],
                    'name' => (string) $rule['name'],
                    'hanzi' => (string) ($rule['hanzi'] ?? ''),
                    'source' => (string) ($rule['source'] ?? 'Tử Vi Nam Phái'),
                    'evidence' => $evidence,
                    'plain_explanation' => (string) ($rule['plain_explanation'] ?? ''),
                    'expert_explanation' => (string) ($rule['expert_explanation'] ?? ''),
                    'provenance' => 'school_rule',
                ];
            }
        }

        return $matched;
    }

    /**
     * @return array{label: string, summary: string}
     */
    protected function describeElementDynamic(string $elA, string $elB): array
    {
        $generates = ['Kim' => 'Thủy', 'Thủy' => 'Mộc', 'Mộc' => 'Hỏa', 'Hỏa' => 'Thổ', 'Thổ' => 'Kim'];

        if ($elA === $elB) {
            return [
                'label' => "Tương Hòa ({$elA} – {$elB})",
                'summary' => "Hai bản mệnh cùng hành {$elA}: dễ tìm tiếng nói chung về giá trị sống và nhịp sinh hoạt; cần tránh giữ cái tôi quá giống nhau khi bất đồng.",
            ];
        }
        if (($generates[$elA] ?? '') === $elB || ($generates[$elB] ?? '') === $elA) {
            return [
                'label' => "Tương Sinh Bổ Trợ ({$elA} – {$elB})",
                'summary' => "Năng lượng bản mệnh {$elA} và {$elB} bổ trợ tự nhiên cho nhau: một người tạo điểm tựa để người kia phát triển; nên giữ sự cân bằng cho – nhận hai chiều.",
            ];
        }

        return [
            'label' => "Khác Biệt Mài Giũa ({$elA} – {$elB})",
            'summary' => "Bản mệnh {$elA} và {$elB} mang hai góc nhìn khác biệt — không đồng nghĩa không hợp nhau, mà là cơ hội bù trừ điểm mù nếu hai bạn giao tiếp cởi mở và tôn trọng nhịp riêng.",
        ];
    }
}
