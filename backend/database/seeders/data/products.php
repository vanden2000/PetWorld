<?php

$article = static function (
    string $title,
    string $introduction,
    array $highlights,
    string $suitableFor,
    string $usage,
    string $storage,
): string {
    $highlightItems = implode('', array_map(
        static fn (string $highlight): string => "<li>{$highlight}</li>",
        $highlights,
    ));

    return <<<HTML
<article>
    <h2>{$title}</h2>
    <p>{$introduction}</p>

    <h3>Điểm nổi bật</h3>
    <ul>{$highlightItems}</ul>

    <h3>Phù hợp với đối tượng nào?</h3>
    <p>{$suitableFor}</p>

    <h3>Hướng dẫn sử dụng</h3>
    <p>{$usage}</p>

    <h3>Bảo quản và lưu ý</h3>
    <p>{$storage}</p>
    <p>Thông tin chi tiết về thành phần, khẩu phần và hạn sử dụng được in trên bao bì. Hãy điều chỉnh cách dùng theo thể trạng thực tế của thú cưng và tham khảo bác sĩ thú y khi cần thiết.</p>
</article>
HTML;
};

return [
    [
        'name' => 'Royal Canin Mini Adult',
        'slug' => 'royal-canin-mini-adult',
        'category_slug' => 'thuc-an-hat',
        'brand_slug' => 'royal-canin',
        'description' => $article(
            'Dinh dưỡng hằng ngày cho chó trưởng thành giống nhỏ',
            'Royal Canin Mini Adult là thức ăn hạt được thiết kế cho nhịp sống và nhu cầu dinh dưỡng của chó trưởng thành có vóc dáng nhỏ. Kích thước hạt vừa miệng giúp bữa ăn thuận tiện hơn, đồng thời hương vị hấp dẫn hỗ trợ duy trì thói quen ăn uống đều đặn.',
            ['Hạt có kích thước phù hợp với khuôn miệng nhỏ.', 'Công thức cân bằng để sử dụng trong khẩu phần hằng ngày.', 'Bao bì tiện bảo quản và dễ định lượng theo từng bữa.'],
            'Phù hợp với chó trưởng thành giống nhỏ. Cần lựa chọn khẩu phần dựa trên cân nặng, mức vận động và tình trạng sức khỏe của từng bé.',
            'Chia lượng ăn khuyến nghị thành các bữa hợp lý trong ngày và luôn chuẩn bị nước sạch. Khi đổi từ thức ăn cũ, nên chuyển đổi từ từ trong nhiều ngày để hệ tiêu hóa làm quen.',
            'Đóng kín miệng túi sau khi mở, để nơi khô ráo và tránh ánh nắng trực tiếp. Không sử dụng khi sản phẩm có mùi hoặc màu sắc bất thường.',
        ),
    ],
    [
        'name' => 'Whiskas Adult vị cá biển',
        'slug' => 'whiskas-adult-vi-ca-bien',
        'category_slug' => 'thuc-an-hat',
        'brand_slug' => 'whiskas',
        'description' => $article(
            'Bữa ăn vị cá biển hấp dẫn cho mèo trưởng thành',
            'Whiskas Adult vị cá biển mang đến một lựa chọn tiện lợi cho bữa ăn hằng ngày của mèo. Hạt khô dễ chia khẩu phần, có thể dùng độc lập hoặc kết hợp hợp lý cùng thức ăn ướt để tạo sự đa dạng cho thực đơn.',
            ['Hương vị cá biển quen thuộc với nhiều chú mèo.', 'Dạng hạt tiện định lượng và giữ vệ sinh khu vực ăn.', 'Phù hợp để xây dựng lịch ăn ổn định mỗi ngày.'],
            'Dành cho mèo trưởng thành có chế độ sinh hoạt bình thường. Với mèo có nhu cầu dinh dưỡng đặc biệt, nên tham khảo tư vấn chuyên môn trước khi sử dụng lâu dài.',
            'Cân khẩu phần theo hướng dẫn trên bao bì và cân nặng của mèo. Luôn để sẵn nước sạch vì thức ăn hạt có độ ẩm thấp.',
            'Bảo quản trong hộp kín hoặc đóng kỹ túi sau mỗi lần dùng. Tránh nơi nóng ẩm, côn trùng và nguồn có mùi mạnh.',
        ),
    ],
    [
        'name' => 'Pate Royal Canin Mini Puppy',
        'slug' => 'pate-royal-canin-mini-puppy',
        'category_slug' => 'pate',
        'brand_slug' => 'royal-canin',
        'description' => $article(
            'Kết cấu mềm dành cho chó con giống nhỏ',
            'Pate Royal Canin Mini Puppy có kết cấu mềm và độ ẩm cao, thuận tiện cho chó con trong giai đoạn làm quen với thức ăn hoàn chỉnh. Sản phẩm có thể dùng làm bữa chính theo hướng dẫn hoặc kết hợp cùng hạt phù hợp.',
            ['Kết cấu mềm giúp chó con dễ tiếp cận bữa ăn.', 'Dạng thức ăn ướt góp phần bổ sung độ ẩm cho khẩu phần.', 'Có thể chia thành nhiều bữa nhỏ trong ngày.'],
            'Phù hợp với chó con giống nhỏ trong độ tuổi được nhà sản xuất ghi trên bao bì. Không dùng thay thế sữa cho chó con chưa đến giai đoạn ăn dặm.',
            'Cho ăn ở nhiệt độ phòng và dùng dụng cụ sạch. Phần thức ăn đã lấy ra không nên để lâu ngoài môi trường, đặc biệt khi thời tiết nóng.',
            'Sản phẩm chưa mở cần để nơi khô mát. Sau khi mở, đậy kín, bảo quản lạnh và sử dụng trong thời gian được khuyến nghị trên bao bì.',
        ),
    ],
    [
        'name' => 'Pate Me-O cá ngừ',
        'slug' => 'pate-me-o-ca-ngu',
        'category_slug' => 'pate',
        'brand_slug' => 'me-o',
        'description' => $article(
            'Pate cá ngừ mềm thơm cho bữa ăn của mèo',
            'Pate Me-O cá ngừ là lựa chọn thức ăn ướt tiện dụng, có hương vị cá và kết cấu mềm dễ ăn. Sản phẩm giúp thực đơn của mèo phong phú hơn, nhất là khi được kết hợp đúng cách cùng khẩu phần hạt hằng ngày.',
            ['Kết cấu mềm, dễ trộn với thức ăn khác.', 'Độ ẩm cao hơn thức ăn khô.', 'Quy cách tiện chia theo từng bữa ăn.'],
            'Phù hợp với mèo ở độ tuổi được ghi trên nhãn sản phẩm. Chủ nuôi cần kiểm tra thành phần nếu mèo từng có phản ứng với nguyên liệu từ cá.',
            'Dùng lượng phù hợp với cân nặng và tổng năng lượng trong ngày. Khi kết hợp với hạt, cần giảm lượng hạt tương ứng để tránh cho ăn quá mức.',
            'Không dùng sản phẩm có bao bì phồng, rách hoặc mùi bất thường. Phần còn lại sau khi mở phải được bảo quản lạnh và dùng sớm.',
        ),
    ],
    [
        'name' => 'Pedigree Dentastix',
        'slug' => 'pedigree-dentastix',
        'category_slug' => 'snack',
        'brand_slug' => 'pedigree',
        'description' => $article(
            'Bánh thưởng nhai dành cho chó',
            'Pedigree Dentastix là snack dạng thanh được dùng như phần thưởng trong lịch sinh hoạt của chó. Hình dạng thanh tạo trải nghiệm nhai thú vị, phù hợp cho những khoảnh khắc huấn luyện hoặc gắn kết giữa chủ nuôi và thú cưng.',
            ['Dạng thanh dễ cầm và chia theo hướng dẫn.', 'Tạo hoạt động nhai giúp chó bớt nhàm chán.', 'Thuận tiện dùng như phần thưởng có kiểm soát.'],
            'Chọn đúng kích cỡ sản phẩm theo cân nặng và độ tuổi của chó. Không phù hợp với chó quá nhỏ hoặc có khó khăn khi nhai và nuốt.',
            'Cho chó dùng dưới sự quan sát của người lớn và luôn chuẩn bị nước sạch. Snack chỉ là phần bổ sung, không thay thế bữa ăn cân bằng.',
            'Đóng kín bao bì để hạn chế hút ẩm. Không cho ăn nếu thanh bánh bị mốc, đổi màu hoặc đã quá hạn sử dụng.',
        ),
    ],
    [
        'name' => 'SmartHeart Creamy Treat',
        'slug' => 'smartheart-creamy-treat',
        'category_slug' => 'snack',
        'brand_slug' => 'smartheart',
        'description' => $article(
            'Snack dạng kem cho phút thưởng vui vẻ',
            'SmartHeart Creamy Treat có dạng kem mềm, thuận tiện dùng trực tiếp, cho vào bát hoặc trộn một lượng vừa phải với thức ăn. Kết cấu này đặc biệt hữu ích khi chủ nuôi muốn tạo trải nghiệm thưởng nhẹ nhàng và gần gũi.',
            ['Dạng kem mềm và dễ chia thành lượng nhỏ.', 'Có thể dùng trực tiếp hoặc trộn vào bữa ăn.', 'Gói nhỏ thuận tiện mang theo và bảo quản.'],
            'Sử dụng cho thú cưng đúng loài và độ tuổi được ghi trên bao bì. Cần đọc kỹ thành phần nếu thú cưng có tiền sử dị ứng thực phẩm.',
            'Cho ăn với lượng vừa phải và tính vào tổng khẩu phần trong ngày. Không để thú cưng cắn nuốt bao bì khi ăn trực tiếp từ gói.',
            'Để nơi khô mát trước khi mở. Gói đã mở nên dùng hết sớm; không sử dụng phần thức ăn đã để lâu ở nhiệt độ phòng.',
        ),
    ],
    [
        'name' => 'Dây dắt Trixie Premium',
        'slug' => 'day-dat-trixie-premium',
        'category_slug' => 'phu-kien',
        'brand_slug' => 'trixie',
        'description' => $article(
            'Kiểm soát an toàn hơn trong mỗi chuyến đi dạo',
            'Dây dắt Trixie Premium là phụ kiện hỗ trợ chủ nuôi kiểm soát thú cưng khi đi bộ, di chuyển ở khu vực công cộng hoặc tập các thói quen cơ bản. Thiết kế hướng đến sự gọn nhẹ, dễ thao tác và phù hợp với nhu cầu sử dụng thường ngày.',
            ['Tay cầm thuận tiện khi di chuyển.', 'Móc nối giúp kết hợp với vòng cổ hoặc đai ngực tương thích.', 'Phù hợp cho hoạt động đi dạo và huấn luyện cơ bản.'],
            'Chọn dây theo cân nặng, lực kéo và kích thước của thú cưng. Sản phẩm không thay thế thiết bị chuyên dụng cho thú cưng có lực kéo đặc biệt mạnh.',
            'Kiểm tra móc khóa, đường may và tình trạng dây trước mỗi lần sử dụng. Giữ khoảng cách an toàn với phương tiện, động vật lạ và khu vực nguy hiểm.',
            'Lau sạch sau khi dùng ở môi trường bẩn hoặc ẩm, sau đó phơi khô tự nhiên. Ngừng sử dụng khi dây bị sờn, móc biến dạng hoặc đường may bung.',
        ),
    ],
    [
        'name' => 'Bát ăn inox Trixie',
        'slug' => 'bat-an-inox-trixie',
        'category_slug' => 'phu-kien',
        'brand_slug' => 'trixie',
        'description' => $article(
            'Bát inox gọn gàng cho khu vực ăn uống',
            'Bát ăn inox Trixie phù hợp để đựng thức ăn hoặc nước uống cho thú cưng. Bề mặt nhẵn giúp việc vệ sinh hằng ngày thuận tiện hơn và kiểu dáng đơn giản dễ bố trí trong nhiều không gian sống.',
            ['Bề mặt nhẵn, dễ rửa sạch sau bữa ăn.', 'Có thể dùng cho thức ăn khô, thức ăn ướt hoặc nước.', 'Nhiều kích thước để lựa chọn theo vóc dáng thú cưng.'],
            'Chọn dung tích và chiều cao bát phù hợp với loài, kích thước cơ thể và thói quen ăn uống. Mỗi thú cưng nên có dụng cụ ăn riêng.',
            'Rửa bát trước lần sử dụng đầu tiên và sau mỗi bữa ăn. Thay nước uống thường xuyên, không để thức ăn ướt tồn đọng lâu trong bát.',
            'Để bát khô ráo khi không sử dụng. Không tiếp tục dùng nếu bề mặt xuất hiện cạnh sắc, biến dạng hoặc hư hỏng có thể gây nguy hiểm.',
        ),
    ],
    [
        'name' => 'KONG Classic',
        'slug' => 'kong-classic',
        'category_slug' => 'do-choi',
        'brand_slug' => 'kong',
        'description' => $article(
            'Đồ chơi tương tác cho những giờ vận động',
            'KONG Classic có hình dáng đặc trưng, tạo chuyển động khó đoán khi nảy và giúp trò chơi trở nên thú vị hơn. Khoang rỗng có thể được sử dụng theo hướng dẫn của nhà sản xuất để tăng tính tương tác trong thời gian chơi.',
            ['Chuyển động nảy tạo trải nghiệm chơi đa dạng.', 'Có thể kết hợp với phần thưởng phù hợp.', 'Nhiều kích cỡ phục vụ các vóc dáng và lực nhai khác nhau.'],
            'Cần chọn đúng kích cỡ và độ bền theo cân nặng, độ tuổi và thói quen nhai của chó. Không có đồ chơi nào hoàn toàn không thể phá hỏng.',
            'Quan sát thú cưng trong khi chơi, đặc biệt ở lần sử dụng đầu tiên. Thu hồi đồ chơi ngay nếu xuất hiện mảnh rời hoặc dấu hiệu hư hỏng đáng kể.',
            'Rửa sạch phần thức ăn còn lại và để khô sau khi dùng. Kiểm tra bề mặt định kỳ, thay mới khi sản phẩm nứt hoặc bị cắn rách.',
        ),
    ],
    [
        'name' => 'Bóng Trixie Denta Fun',
        'slug' => 'bong-trixie-denta-fun',
        'category_slug' => 'do-choi',
        'brand_slug' => 'trixie',
        'description' => $article(
            'Quả bóng nhai cho trò chơi chủ động',
            'Bóng Trixie Denta Fun kết hợp hình thức quả bóng quen thuộc với bề mặt tạo cảm giác nhai thú vị. Sản phẩm phù hợp cho trò ném bắt, chơi tương tác hoặc giúp chó tự giải trí trong khoảng thời gian ngắn.',
            ['Hình cầu phù hợp với hoạt động ném và nhặt.', 'Bề mặt tạo thêm trải nghiệm khi chó ngậm và nhai.', 'Thiết kế gọn, dễ mang theo khi ra ngoài.'],
            'Chọn đường kính bóng đủ lớn để chó không thể nuốt. Cân nhắc lực nhai và thay đổi đồ chơi nếu chất liệu không phù hợp với thói quen của thú cưng.',
            'Chỉ cho chơi dưới sự giám sát. Không ném bóng về phía đường giao thông, khu vực cao hoặc nơi có vật cản nguy hiểm.',
            'Rửa và làm khô sau khi chơi ngoài trời. Loại bỏ sản phẩm nếu bị nứt, bong mảnh hoặc biến dạng để tránh nguy cơ nuốt dị vật.',
        ),
    ],
    [
        'name' => 'Xịt khử mùi Bioline',
        'slug' => 'xit-khu-mui-bioline',
        'category_slug' => 've-sinh-va-cham-soc',
        'brand_slug' => 'bioline',
        'description' => $article(
            'Giữ không gian sinh hoạt của thú cưng dễ chịu hơn',
            'Xịt khử mùi Bioline được dùng để hỗ trợ vệ sinh và kiểm soát mùi tại khu vực sinh hoạt của thú cưng theo hướng dẫn trên nhãn. Sản phẩm phù hợp cho quy trình dọn dẹp định kỳ, bên cạnh việc loại bỏ chất bẩn và làm sạch bề mặt đúng cách.',
            ['Dạng xịt giúp phân bố dung dịch thuận tiện.', 'Phù hợp bổ sung vào quy trình vệ sinh thường xuyên.', 'Thiết kế chai dễ cất giữ và sử dụng khi cần.'],
            'Chỉ sử dụng trên đối tượng và bề mặt được nhà sản xuất chỉ định. Thử ở vùng nhỏ, khuất trước khi dùng rộng và tránh dùng khi thú cưng có dấu hiệu nhạy cảm với thành phần.',
            'Làm sạch chất bẩn trước, sau đó sử dụng đúng khoảng cách và lượng ghi trên bao bì. Tránh xịt vào mắt, mũi, miệng, thức ăn, nước uống hoặc trực tiếp lên thú cưng nếu nhãn không cho phép.',
            'Đậy nắp sau khi dùng, để xa trẻ em và thú cưng. Bảo quản tránh nhiệt độ cao; ngừng sử dụng nếu có dấu hiệu kích ứng hoặc phản ứng bất thường.',
        ),
    ],
    [
        'name' => 'Sữa tắm Bioline',
        'slug' => 'sua-tam-bioline',
        'category_slug' => 've-sinh-va-cham-soc',
        'brand_slug' => 'bioline',
        'description' => $article(
            'Chăm sóc da và lông trong mỗi lần tắm',
            'Sữa tắm Bioline hỗ trợ làm sạch bụi bẩn trên da và lông thú cưng khi được sử dụng đúng hướng dẫn. Một quy trình tắm nhẹ nhàng, nhiệt độ nước phù hợp và thao tác sấy khô cẩn thận sẽ giúp thú cưng thoải mái hơn.',
            ['Dạng lỏng dễ hòa và phân bố trên bộ lông ướt.', 'Phù hợp cho quy trình vệ sinh định kỳ.', 'Có thể kết hợp với chải lông để chăm sóc toàn diện hơn.'],
            'Chọn đúng sản phẩm dành cho loài, độ tuổi và tình trạng da lông của thú cưng. Không tự dùng trên vùng da tổn thương hoặc thú cưng đang điều trị nếu chưa có tư vấn chuyên môn.',
            'Làm ướt lông bằng nước ấm, dùng lượng vừa đủ, massage nhẹ và xả thật sạch. Tránh để bọt tiếp xúc với mắt, tai, mũi và miệng; lau và sấy khô sau khi tắm.',
            'Đóng chặt nắp và bảo quản nơi mát, tránh ánh nắng. Nếu thú cưng ngứa, đỏ da hoặc có biểu hiện bất thường, ngừng dùng và liên hệ bác sĩ thú y.',
        ),
    ],
];
