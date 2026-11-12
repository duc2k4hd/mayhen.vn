/* MÃ‚Y Háº¸N DOCS | Pure JavaScript, no external dependencies.
   Demo content is intentionally local. Connect live docs, help requests and auth in Laravel. */
(() => {
  'use strict';
  const root = document.getElementById('mayhen_docs_app');
  if (!root) return;
  const $ = (selector) => root.querySelector(selector);
  const $$ = (selector) => Array.from(root.querySelectorAll(selector));

  const GUIDES = {
    'intro': {category:'Báº¯t Ä‘áº§u',title:'MÃ¢y Háº¹n lÃ  gÃ¬?',summary:'Má»™t khÃ´ng gian Ä‘á»ƒ thiáº¿t káº¿ lá»i má»i trá»±c tuyáº¿n, tá»• chá»©c thÃ´ng tin ngÃ y cÆ°á»›i vÃ  káº¿t ná»‘i vá»›i nhá»¯ng ngÆ°á»i thÃ¢n thÆ°Æ¡ng.',tags:'giá»›i thiá»‡u tÃ i liá»‡u thiá»‡p cÆ°á»›i báº¯t Ä‘áº§u',steps:['KhÃ¡m phÃ¡ cÃ¡c nhÃ³m hÆ°á»›ng dáº«n vá» thiáº¿t káº¿, khÃ¡ch má»i vÃ  chia sáº».','XÃ¡c Ä‘á»‹nh phong cÃ¡ch thiá»‡p mong muá»‘n: tá»‘i giáº£n, cá»• Ä‘iá»ƒn, lÃ£ng máº¡n hoáº·c hiá»‡n Ä‘áº¡i.','Chuáº©n bá»‹ nhá»¯ng thÃ´ng tin cáº§n thiáº¿t: tÃªn hai báº¡n, thá»i gian, Ä‘á»‹a Ä‘iá»ƒm vÃ  má»™t vÃ i hÃ¬nh áº£nh.','Báº¯t Ä‘áº§u tá»« hÆ°á»›ng dáº«n táº¡o thiá»‡p Ä‘áº§u tiÃªn, sau Ä‘Ã³ kiá»ƒm tra giao diá»‡n trÃªn Ä‘iá»‡n thoáº¡i.'],tip:'Äá»«ng cá»‘ hoÃ n thiá»‡n má»i chi tiáº¿t ngay láº§n Ä‘áº§u. Báº¡n cÃ³ thá»ƒ báº¯t Ä‘áº§u báº±ng má»™t máº«u Ä‘Æ¡n giáº£n.'},
    'first-card': {category:'Báº¯t Ä‘áº§u',title:'Táº¡o thiá»‡p cÆ°á»›i online Ä‘áº§u tiÃªn',summary:'Lá»™ trÃ¬nh ngáº¯n gá»n Ä‘á»ƒ biáº¿n Ã½ tÆ°á»Ÿng thÃ nh má»™t táº¥m thiá»‡p cÆ°á»›i cÃ³ thá»ƒ xem trÆ°á»›c vÃ  chuáº©n bá»‹ chia sáº».',tags:'táº¡o thiá»‡p Ä‘áº§u tiÃªn quickstart má»›i báº¯t Ä‘áº§u 5 phÃºt',steps:['Chuáº©n bá»‹ tÃªn cÃ´ dÃ¢u, chÃº rá»ƒ, ngÃ y cÆ°á»›i vÃ  thÃ´ng tin Ä‘á»‹a Ä‘iá»ƒm.','Chá»n má»™t máº«u thiá»‡p phÃ¹ há»£p vá»›i tÃ´ng mÃ u vÃ  phong cÃ¡ch buá»•i lá»….','Thay ná»™i dung minh há»a báº±ng thÃ´ng tin cá»§a hai báº¡n; kiá»ƒm tra chÃ­nh táº£ vÃ  mÃºi giá».','ThÃªm áº£nh, lá»i nháº¯n vÃ  nhá»¯ng chi tiáº¿t cáº§n thiáº¿t, trÃ¡nh khiáº¿n thiá»‡p quÃ¡ dÃ i.','Xem trÆ°á»›c trÃªn cáº£ mÃ¡y tÃ­nh láº«n Ä‘iá»‡n thoáº¡i; xuáº¥t báº£n khi Ä‘Ã£ kiá»ƒm tra xong.'],tip:'NÃªn gá»­i liÃªn káº¿t xem thá»­ cho ngÆ°á»i thÃ¢n tin cáº­y Ä‘á»ƒ nhá» rÃ  soÃ¡t trÆ°á»›c khi phÃ¡t hÃ nh rá»™ng rÃ£i.'},
    'pick-template': {category:'Thiáº¿t káº¿',title:'Chá»n máº«u thiá»‡p phÃ¹ há»£p',summary:'CÃ¡ch chá»n máº«u thiá»‡p cÆ°á»›i vá»«a Ä‘áº¹p vá»«a dá»… Ä‘á»c trÃªn má»i kÃ­ch thÆ°á»›c mÃ n hÃ¬nh.',tags:'template máº«u thiá»‡p phong cÃ¡ch hiá»‡n Ä‘áº¡i hoa cÆ°á»›i',steps:['Chá»n tÃ´ng mÃ u phÃ¹ há»£p vá»›i khÃ´ng khÃ­ lá»… cÆ°á»›i vÃ  bá»™ nháº­n diá»‡n cá»§a hai báº¡n.','Æ¯u tiÃªn máº«u cÃ³ chá»¯ rÃµ rÃ ng, khoáº£ng tráº¯ng thoÃ¡ng vÃ  vá»‹ trÃ­ hiá»ƒn thá»‹ thá»i gian, Ä‘á»‹a Ä‘iá»ƒm ná»•i báº­t.','Xem thá»­ bá»‘ cá»¥c trÃªn Ä‘iá»‡n thoáº¡i vÃ¬ pháº§n lá»›n khÃ¡ch má»i cÃ³ thá»ƒ má»Ÿ tá»« Ä‘iá»‡n thoáº¡i.','Kiá»ƒm tra áº£nh, hiá»‡u á»©ng vÃ  nháº¡c ná»n cÃ³ há»— trá»£ táº¯t hoáº·c bá» qua.'],tip:'MÃ u kem, há»“ng Ä‘áº¥t vÃ  champagne há»£p vá»›i phong cÃ¡ch thanh lá»‹ch, nhÆ°ng Ä‘á»™ tÆ°Æ¡ng pháº£n váº«n quan trá»ng nháº¥t.'},
    'publish': {category:'Chia sáº»',title:'Xuáº¥t báº£n thiá»‡p cÆ°á»›i',summary:'Nhá»¯ng viá»‡c cáº§n rÃ  soÃ¡t trÆ°á»›c khi Ä‘Æ°a thiá»‡p Ä‘áº¿n vá»›i khÃ¡ch má»i.',tags:'xuáº¥t báº£n Ä‘Äƒng thiá»‡p publish phÃ¡t hÃ nh',steps:['RÃ  soÃ¡t há» tÃªn, ngÃ y giá», Ä‘á»‹a chá»‰, sá»‘ Ä‘iá»‡n thoáº¡i liÃªn há»‡ vÃ  liÃªn káº¿t báº£n Ä‘á»“.','Kiá»ƒm tra thÃ´ng tin RSVP, nÃºt xÃ¡c nháº­n vÃ  kháº£ nÄƒng hiá»ƒn thá»‹ trÃªn mÃ n hÃ¬nh nhá».','XÃ¡c Ä‘á»‹nh dá»¯ liá»‡u nÃ o Ä‘Æ°á»£c cÃ´ng khai vÃ  dá»¯ liá»‡u nÃ o chá»‰ nÃªn giá»¯ riÃªng.','Khi tÃ­ch há»£p backend, lÆ°u báº£n nhÃ¡p vÃ  tráº¡ng thÃ¡i xuáº¥t báº£n riÃªng biá»‡t.','Sau khi xuáº¥t báº£n, thá»­ má»Ÿ liÃªn káº¿t trong cháº¿ Ä‘á»™ áº©n danh trÆ°á»›c khi chia sáº».'],tip:'Báº£n demo hÆ°á»›ng dáº«n nÃ y khÃ´ng cÃ³ chá»©c nÄƒng xuáº¥t báº£n tháº­t; pháº§n Ä‘Ã³ cáº§n káº¿t ná»‘i vá»›i Laravel.'},
    'edit-content': {category:'Thiáº¿t káº¿',title:'Chá»‰nh sá»­a ná»™i dung lá»i má»i',summary:'Sáº¯p xáº¿p cÃ¡c pháº§n thÃ´ng tin quan trá»ng sao cho khÃ¡ch má»i Ä‘á»c nhanh vÃ  khÃ´ng bá» sÃ³t.',tags:'ná»™i dung vÄƒn báº£n lá»i má»i tÃªn ngÃ y giá» Ä‘á»‹a Ä‘iá»ƒm',steps:['Báº¯t Ä‘áº§u báº±ng lá»i chÃ o ngáº¯n gá»n vÃ  tÃªn hai báº¡n.','Ghi rÃµ ngÃ y, giá» báº¯t Ä‘áº§u, Ä‘á»‹a Ä‘iá»ƒm tá»• chá»©c vÃ  hÆ°á»›ng dáº«n di chuyá»ƒn náº¿u cáº§n.','Bá»• sung ná»™i dung vá» dress code, lÆ°u Ã½ khÃ¡ch má»i hoáº·c lá»i nháº¯n riÃªng khi phÃ¹ há»£p.','Äá»c thá»­ trÃªn Ä‘iá»‡n thoáº¡i: tiÃªu Ä‘á» ngáº¯n, Ä‘oáº¡n vÄƒn khÃ´ng quÃ¡ dÃ y vÃ  nÃºt CTA Ä‘á»§ lá»›n.'],tip:'ThÃ´ng tin Ä‘á»‹a Ä‘iá»ƒm vÃ  thá»i gian nÃªn ná»•i báº­t hÆ¡n cÃ¡c Ä‘oáº¡n trang trÃ­.'},
    'fonts-colors': {category:'Thiáº¿t káº¿',title:'MÃ u sáº¯c vÃ  phÃ´ng chá»¯',summary:'Phá»‘i mÃ u thiá»‡p cÆ°á»›i dá»‹u máº¯t nhÆ°ng váº«n giá»¯ Ä‘Æ°á»£c Ä‘á»™ tÆ°Æ¡ng pháº£n vÃ  tÃ­nh nháº¥t quÃ¡n.',tags:'mÃ u sáº¯c font chá»¯ typography há»“ng Ä‘áº¥t champagne',steps:['Chá»n má»™t mÃ u chÃ­nh, má»™t mÃ u ná»n trung tÃ­nh vÃ  má»™t mÃ u nháº¥n vá»«a Ä‘á»§.','Äáº£m báº£o chá»¯ trÃªn ná»n sÃ¡ng hoáº·c tá»‘i cÃ³ Ä‘á»™ tÆ°Æ¡ng pháº£n tá»‘t.','DÃ¹ng Ã­t kiá»ƒu chá»¯: má»™t kiá»ƒu cho tiÃªu Ä‘á», má»™t kiá»ƒu dá»… Ä‘á»c cho ná»™i dung.','Kiá»ƒm tra cá»¡ chá»¯, chiá»u cao dÃ²ng vÃ  nÃºt báº¥m trÃªn mÃ n hÃ¬nh 320px.'],tip:'Trang demo Ä‘ang dÃ¹ng Open Sans tá»« Google Fonts, kÃ¨m font dá»± phÃ²ng khi khÃ´ng cÃ³ máº¡ng.'},
    'photos': {category:'Thiáº¿t káº¿',title:'ThÃªm áº£nh vÃ  album cÆ°á»›i',summary:'Chuáº©n bá»‹ áº£nh sáº¯c nÃ©t vÃ  tá»‘i Æ°u dung lÆ°á»£ng Ä‘á»ƒ thiá»‡p táº£i nhanh.',tags:'áº£nh cÆ°á»›i photo album hÃ¬nh áº£nh ká»· niá»‡m upload',steps:['Chá»n áº£nh cÃ³ sá»± Ä‘á»“ng Ã½ cá»§a ngÆ°á»i xuáº¥t hiá»‡n trong áº£nh.','Cáº¯t áº£nh Ä‘Ãºng tá»· lá»‡ cho vá»‹ trÃ­ hiá»ƒn thá»‹, giá»¯ khuÃ´n máº·t á»Ÿ vÃ¹ng quan trá»ng.','Xuáº¥t WebP hoáº·c AVIF khi phÃ¹ há»£p, giá»›i háº¡n kÃ­ch thÆ°á»›c theo nhu cáº§u sá»­ dá»¥ng.','Bá»• sung mÃ´ táº£ áº£nh khi cáº§n Ä‘á»ƒ há»— trá»£ kháº£ nÄƒng tiáº¿p cáº­n.','Kiá»ƒm tra táº£i áº£nh qua káº¿t ná»‘i di Ä‘á»™ng vÃ  cÃ¡ch hiá»ƒn thá»‹ khi áº£nh lá»—i.'],tip:'KhÃ´ng Ä‘Æ°a áº£nh giáº¥y tá», Ä‘á»‹a chá»‰ nhÃ  riÃªng hoáº·c dá»¯ liá»‡u nháº¡y cáº£m lÃªn thiá»‡p cÃ´ng khai.'},
    'music': {category:'Thiáº¿t káº¿',title:'Nháº¡c ná»n vÃ  hiá»‡u á»©ng',summary:'Káº¿t há»£p chuyá»ƒn Ä‘á»™ng vÃ  Ã¢m thanh nháº¹ nhÃ ng mÃ  khÃ´ng lÃ m phiá»n khÃ¡ch má»i.',tags:'nháº¡c ná»n Ã¢m nháº¡c chuyá»ƒn Ä‘á»™ng animation hiá»‡u á»©ng',steps:['Æ¯u tiÃªn nháº¡c cÃ³ quyá»n sá»­ dá»¥ng há»£p lá»‡.','Cung cáº¥p nÃºt báº­t/táº¯t rÃµ rÃ ng; khÃ´ng phá»¥ thuá»™c vÃ o kháº£ nÄƒng tá»± phÃ¡t cá»§a trÃ¬nh duyá»‡t.','Giá»¯ chuyá»ƒn Ä‘á»™ng tinh táº¿, ngáº¯n gá»n vÃ  trÃ¡nh lÃ m máº¥t táº­p trung khi Ä‘á»c.','TÃ´n trá»ng tÃ¹y chá»n giáº£m chuyá»ƒn Ä‘á»™ng trong thiáº¿t bá»‹ cá»§a ngÆ°á»i xem.'],tip:'TrÃ¬nh duyá»‡t thÆ°á»ng giá»›i háº¡n autoplay cÃ³ Ã¢m thanh; hÃ£y Ä‘á»ƒ ngÆ°á»i xem chá»§ Ä‘á»™ng phÃ¡t.'},
    'rsvp': {category:'KhÃ¡ch má»i',title:'Thiáº¿t láº­p xÃ¡c nháº­n tham dá»± (RSVP)',summary:'Má»™t biá»ƒu máº«u ngáº¯n, dá»… dÃ¹ng giÃºp báº¡n thu tháº­p xÃ¡c nháº­n tham dá»± rÃµ rÃ ng.',tags:'rsvp tham dá»± xÃ¡c nháº­n khÃ¡ch má»i Ä‘á»“ng Ã½ tá»« chá»‘i',steps:['XÃ¡c Ä‘á»‹nh nhá»¯ng trÆ°á»ng cáº§n thiáº¿t: tÃªn khÃ¡ch, lá»±a chá»n tham dá»± vÃ  sá»‘ ngÆ°á»i Ä‘i cÃ¹ng náº¿u Ä‘Æ°á»£c phÃ©p.','ThÃªm ngÃ y chá»‘t pháº£n há»“i vÃ  lá»i cáº£m Æ¡n thÃ¢n thiá»‡n sau khi gá»­i.','Äáº·t cÃ¢u há»i ngáº¯n vÃ  phÃ¹ há»£p trÃªn Ä‘iá»‡n thoáº¡i.','Trong backend Laravel, xá»­ lÃ½ trÃ¹ng pháº£n há»“i, giá»›i háº¡n tá»‘c Ä‘á»™ vÃ  kiá»ƒm tra CSRF khi cáº§n.','Chá»‰ thu tháº­p dá»¯ liá»‡u thá»±c sá»± cáº§n thiáº¿t, cÃ´ng bá»‘ rÃµ cÃ¡ch sá»­ dá»¥ng thÃ´ng tin.'],tip:'KhÃ´ng nÃªn yÃªu cáº§u khÃ¡ch nháº­p quÃ¡ nhiá»u thÃ´ng tin cho má»™t lá»i xÃ¡c nháº­n Ä‘Æ¡n giáº£n.'},
    'guest-list': {category:'KhÃ¡ch má»i',title:'Quáº£n lÃ½ danh sÃ¡ch khÃ¡ch má»i',summary:'Sáº¯p xáº¿p khÃ¡ch má»i vÃ  tráº¡ng thÃ¡i xÃ¡c nháº­n Ä‘á»ƒ theo dÃµi cÃ´ng viá»‡c dá»… dÃ ng.',tags:'khÃ¡ch má»i danh sÃ¡ch group thá»‘ng kÃª sá»± kiá»‡n',steps:['Láº­p danh sÃ¡ch theo nhÃ³m nhÆ° gia Ä‘Ã¬nh, báº¡n bÃ¨, Ä‘á»“ng nghiá»‡p.','Chuáº©n hÃ³a tÃªn vÃ  thÃ´ng tin liÃªn há»‡ trÆ°á»›c khi nháº­p dá»¯ liá»‡u.','Theo dÃµi tráº¡ng thÃ¡i chÆ°a tráº£ lá»i, tham dá»± vÃ  khÃ´ng tham dá»±.','Äáº·t quyá»n truy cáº­p danh sÃ¡ch cho nhá»¯ng ngÆ°á»i cÃ³ trÃ¡ch nhiá»‡m tá»• chá»©c.'],tip:'Danh sÃ¡ch khÃ¡ch má»i lÃ  dá»¯ liá»‡u riÃªng tÆ°; khÃ´ng cÃ´ng khai trong mÃ£ nguá»“n frontend.'},
    'checkin': {category:'KhÃ¡ch má»i',title:'Check-in khÃ¡ch má»i báº±ng QR',summary:'Gá»£i Ã½ quy trÃ¬nh Ä‘Ã³n tiáº¿p vá»›i mÃ£ QR duy nháº¥t vÃ  tráº¡ng thÃ¡i check-in theo tá»«ng khÃ¡ch.',tags:'qr check in kiá»ƒm tra mÃ£ khÃ¡ch má»i quÃ©t',steps:['Táº¡o mÃ£ Ä‘á»‹nh danh khÃ´ng dá»… Ä‘oÃ¡n cho tá»«ng vÃ© má»i trong backend.','XÃ¡c thá»±c QR vá»›i mÃ¡y chá»§ trÆ°á»›c khi Ä‘Ã¡nh dáº¥u Ä‘Ã£ Ä‘áº¿n.','LÆ°u thá»i gian vÃ  ngÆ°á»i thao tÃ¡c Ä‘á»ƒ dá»… Ä‘á»‘i soÃ¡t.','Cho phÃ©p phÆ°Æ¡ng Ã¡n tÃ¬m thá»§ cÃ´ng náº¿u khÃ¡ch khÃ´ng má»Ÿ Ä‘Æ°á»£c mÃ£.'],tip:'KhÃ´ng nÃªn lÆ°u trá»±c tiáº¿p dá»¯ liá»‡u riÃªng tÆ° dÆ°á»›i dáº¡ng QR khÃ´ng báº£o vá»‡.'},
    'wishes': {category:'KhÃ¡ch má»i',title:'Sá»• lÆ°u bÃºt lá»i chÃºc',summary:'Táº¡o khÃ´ng gian Ä‘á»ƒ khÃ¡ch má»i gá»­i nhá»¯ng lá»i chÃºc chÃ¢n thÃ nh.',tags:'lá»i chÃºc comment khÃ¡ch lÆ°u bÃºt gallery ká»· niá»‡m',steps:['Äáº·t khu vá»±c lá»i chÃºc á»Ÿ vá»‹ trÃ­ khÃ¡ch dá»… tÃ¬m.','Giá»¯ biá»ƒu máº«u ngáº¯n: tÃªn hiá»ƒn thá»‹ vÃ  lá»i nháº¯n.','CÃ³ bÆ°á»›c kiá»ƒm duyá»‡t, lá»c spam vÃ  giá»›i háº¡n táº§n suáº¥t á»Ÿ backend.','Sao lÆ°u lá»i chÃºc Ä‘á»‹nh ká»³ Ä‘á»ƒ báº£o vá»‡ nhá»¯ng ká»· niá»‡m Ä‘áº¹p.'],tip:'KhÃ´ng hiá»ƒn thá»‹ ná»™i dung ngÆ°á»i dÃ¹ng gá»­i dÆ°á»›i dáº¡ng HTML chÆ°a Ä‘Æ°á»£c lÃ m sáº¡ch.'},
    'share': {category:'Chia sáº»',title:'Chia sáº» thiá»‡p cÆ°á»›i',summary:'Chá»n kÃªnh gá»­i phÃ¹ há»£p vÃ  Ä‘áº£m báº£o liÃªn káº¿t má»Ÿ nhanh, dá»… hiá»ƒu.',tags:'chia sáº» zalo facebook messenger copy link lá»i má»i',steps:['Kiá»ƒm tra báº£n xem trÆ°á»›c vÃ  thÃ´ng tin trÆ°á»›c khi gá»­i cho khÃ¡ch.','Táº¡o liÃªn káº¿t ngáº¯n gá»n, á»•n Ä‘á»‹nh vÃ  cÃ³ giao diá»‡n chia sáº» thÃ¢n thiá»‡n.','Thá»­ má»Ÿ liÃªn káº¿t trÃªn Ä‘iá»‡n thoáº¡i báº±ng trÃ¬nh duyá»‡t khÃ¡c.','Háº¡n cháº¿ chia sáº» cÃ´ng khai náº¿u trang chá»©a thÃ´ng tin riÃªng tÆ°.'],tip:'Báº¡n cÃ³ thá»ƒ dÃ¹ng nÃºt â€œChia sáº» trangâ€ trong footer Ä‘á»ƒ thá»­ tÃ­nh nÄƒng chia sáº» tÃ i liá»‡u nÃ y.'},
    'privacy': {category:'Chia sáº»',title:'Báº£o máº­t vÃ  quyá»n riÃªng tÆ°',summary:'Nhá»¯ng nguyÃªn táº¯c giÃºp báº£o vá»‡ dá»¯ liá»‡u ngÆ°á»i dÃ¹ng, khÃ¡ch má»i vÃ  nhá»¯ng khoáº£nh kháº¯c riÃªng tÆ°.',tags:'báº£o máº­t riÃªng tÆ° quyá»n truy cáº­p personal data thÃ´ng tin cÃ¡ nhÃ¢n',steps:['PhÃ¢n loáº¡i dá»¯ liá»‡u cÃ´ng khai vÃ  dá»¯ liá»‡u chá»‰ dÃ nh cho ngÆ°á»i tá»• chá»©c.','XÃ¡c thá»±c vÃ  phÃ¢n quyá»n phÃ­a server thay vÃ¬ chá»‰ áº©n pháº§n tá»­ báº±ng CSS.','DÃ¹ng HTTPS, kiá»ƒm tra Ä‘áº§u vÃ o vÃ  giá»›i háº¡n truy cáº­p API.','ÄÆ°a ra chÃ­nh sÃ¡ch lÆ°u trá»¯, xÃ³a dá»¯ liá»‡u vÃ  thÃ´ng bÃ¡o quyá»n riÃªng tÆ° minh báº¡ch.'],tip:'KhÃ´ng Ä‘áº·t khÃ³a API bÃ­ máº­t hoáº·c dá»¯ liá»‡u khÃ¡ch má»i trong JavaScript cá»§a trang.'},
    'custom-link': {category:'Chia sáº»',title:'TÃ¹y chá»‰nh liÃªn káº¿t thiá»‡p',summary:'Táº¡o liÃªn káº¿t dá»… nhá»›, nháº¥t quÃ¡n vÃ  trÃ¡nh xung Ä‘á»™t vá»›i cÃ¡c Ä‘á»‹a chá»‰ Ä‘Ã£ tá»“n táº¡i.',tags:'slug Ä‘Æ°á»ng dáº«n domain tÃªn miá»n seo liÃªn káº¿t',steps:['Chá»n slug ngáº¯n, dá»… gÃµ vÃ  phÃ¹ há»£p vá»›i cáº·p Ä‘Ã´i.','Giá»›i háº¡n kÃ½ tá»± há»£p lá»‡ vÃ  kiá»ƒm tra trÃ¹ng á»Ÿ backend.','Thiáº¿t láº­p canonical, metadata vÃ  hÃ¬nh xem trÆ°á»›c cho trang cÃ´ng khai.','Khi thay Ä‘á»•i Ä‘Æ°á»ng dáº«n, sá»­ dá»¥ng chuyá»ƒn hÆ°á»›ng 301 náº¿u URL cÅ© Ä‘Ã£ Ä‘Æ°á»£c chia sáº».'],tip:'TrÃ¡nh Ä‘Æ°a sá»‘ Ä‘iá»‡n thoáº¡i hoáº·c dá»¯ liá»‡u nháº­n dáº¡ng nháº¡y cáº£m vÃ o slug.'},
    'account': {category:'Há»— trá»£',title:'TÃ i khoáº£n vÃ  trá»£ giÃºp',summary:'CÃ¡c lÆ°u Ã½ khi quáº£n lÃ½ tÃ i khoáº£n vÃ  tÃ¬m há»— trá»£ trong quÃ¡ trÃ¬nh lÃ m thiá»‡p.',tags:'tÃ i khoáº£n Ä‘Äƒng nháº­p Ä‘Äƒng kÃ½ há»— trá»£ máº­t kháº©u',steps:['Sá»­ dá»¥ng máº­t kháº©u máº¡nh vÃ  khÃ´ng chia sáº» thÃ´ng tin Ä‘Äƒng nháº­p.','Náº¿u quÃªn máº­t kháº©u, dÃ¹ng luá»“ng khÃ´i phá»¥c cÃ³ giá»›i háº¡n thá»i gian vÃ  mÃ£ má»™t láº§n.','KhÃ´ng gá»­i máº­t kháº©u hoáº·c mÃ£ OTP trong ná»™i dung gÃ³p Ã½.','Sá»­ dá»¥ng Ã´ tÃ¬m kiáº¿m tÃ i liá»‡u vÃ  chá»©c nÄƒng gÃ³p Ã½ Ä‘á»ƒ ghi nháº­n váº¥n Ä‘á» cáº§n bá»• sung.'],tip:'CÃ¡c chá»©c nÄƒng tÃ i khoáº£n tháº­t cáº§n tÃ­ch há»£p vá»›i há»‡ thá»‘ng xÃ¡c thá»±c Laravel.'}
  };
  const guideIds = Object.keys(GUIDES);
  const CHECKLIST = ['Chá»‘t ngÃ y, giá» vÃ  Ä‘á»‹a Ä‘iá»ƒm lá»… cÆ°á»›i','Chá»n máº«u thiá»‡p phÃ¹ há»£p','Äiá»n Ä‘áº§y Ä‘á»§ thÃ´ng tin lá»i má»i','ThÃªm album vÃ  kiá»ƒm tra áº£nh','Táº¡o danh sÃ¡ch khÃ¡ch má»i','Kiá»ƒm tra biá»ƒu máº«u RSVP','Xem trÆ°á»›c vÃ  chia sáº» thiá»‡p'];
  const SETTINGS_KEY='mayhen_docs_settings_v1';
  const BOOKMARKS_KEY='mayhen_docs_bookmarks_v1';
  const CHECKLIST_KEY='mayhen_docs_checklist_v1';
  const DATE_KEY='mayhen_docs_wedding_date_v1';
  const FEEDBACK_KEY='mayhen_docs_feedback_draft_v1';
  const RATING_KEY='mayhen_docs_rating_v1';
  const store = {
    get(key, fallback) { try {const val=localStorage.getItem(key);return val===null?fallback:JSON.parse(val);} catch {return fallback;} },
    set(key, value) { try {localStorage.setItem(key,JSON.stringify(value));return true;} catch {return false;} }
  };
  let settings = {...{theme:'light',font:16,width:'comfortable',motion:true},...store.get(SETTINGS_KEY,{})};
  if (!['light','dark','system'].includes(settings.theme)) settings.theme='light';
  if (!['comfortable','wide'].includes(settings.width)) settings.width='comfortable';
  settings.font=Math.max(14,Math.min(20,Number(settings.font)||16));
  settings.motion=settings.motion!==false;
  let bookmarks = store.get(BOOKMARKS_KEY,[]);
  if (!Array.isArray(bookmarks)) bookmarks=[];
  bookmarks=bookmarks.filter(id=>GUIDES[id]);
  let tasks=store.get(CHECKLIST_KEY,[]);
  if (!Array.isArray(tasks)) tasks=[];
  tasks=CHECKLIST.map((_,i)=>tasks[i]===true);
  let weddingDate=store.get(DATE_KEY,'');
  if (typeof weddingDate!=='string') weddingDate='';
  let currentGuide='first-card';
  let searchActiveIndex=0;
  let toastTimer;
  let returnFocus=null;
  const systemDark=window.matchMedia?.('(prefers-color-scheme: dark)');

  function toast(message) {
    const el=$('#mayhen_docs_toast');
    if (!el) return;
    window.clearTimeout(toastTimer);
    el.textContent=message;
    el.classList.add('mayhen_docs_toast_show');
    toastTimer=window.setTimeout(()=>el.classList.remove('mayhen_docs_toast_show'),3100);
  }
  function normalize(value){return String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/Ä‘/g,'d').replace(/Ä/g,'D').toLowerCase().trim();}
  function searchGuides(input){
    const stopwords=new Set(['lam','sao','de','toi','co','the','muon','nhu','nao','cach','gi','la','mot','va','cho','cua','ban','duoc','khong','voi','tren','trong','hay','ve','xin','hoi','nhung','nhieu','can','nen','tu']);
    const terms=normalize(input).replace(/[^a-z0-9\s]/g,' ').split(/\s+/).filter(term=>term.length>=2&&!stopwords.has(term));
    if (!terms.length) return ['first-card','rsvp','pick-template','photos','guest-list','privacy','share','wishes'];
    const requiredMatches=terms.length<=2?1:Math.ceil(terms.length*.38);
    return guideIds.map(id=>{
      const g=GUIDES[id];const title=normalize(g.title), tags=normalize(g.tags), desc=normalize(g.summary);
      let score=0,matched=0;
      for(const term of terms){
        if(title.includes(term)){score+=5;matched++;}
        else if(tags.includes(term)){score+=3;matched++;}
        else if(desc.includes(term)){score+=1;matched++;}
      }
      if(matched<requiredMatches)return null;
      if(title.startsWith(normalize(input))) score+=4;
      return {id,score,matched};
    }).filter(Boolean).sort((a,b)=>b.score-a.score||b.matched-a.matched||a.id.localeCompare(b.id)).map(x=>x.id);
  }
  function closeDialog(dialog){if(dialog?.open) dialog.close();}
  function openDialog(id){
    const dlg=document.getElementById(id);if(!dlg || typeof dlg.showModal!=='function') return;
    const opened=root.querySelector('dialog[open]'); if(opened && opened!==dlg) opened.close();
    if(dlg.open) return;
    returnFocus=document.activeElement;
    dlg.showModal();
    if(id==='mayhen_docs_dialog_search') {renderSearch();$('#mayhen_docs_search_input').focus();}
    if(id==='mayhen_docs_dialog_bookmarks') renderBookmarks();
    if(id==='mayhen_docs_dialog_checklist') renderChecklist();
    if(id==='mayhen_docs_dialog_countdown') updateCountdown();
    if(id==='mayhen_docs_dialog_share') $('#mayhen_docs_share_link').value=window.location.href;
    if(id==='mayhen_docs_dialog_settings') updateSettingsUI();
  }
  $$('dialog').forEach(dialog=>{
    dialog.addEventListener('click',event=>{
      if(event.target===dialog){const rect=dialog.getBoundingClientRect();if(event.clientX<rect.left || event.clientX>rect.right || event.clientY<rect.top || event.clientY>rect.bottom)dialog.close();}
    });
    dialog.addEventListener('close',()=>{try {if(returnFocus && returnFocus.isConnected && typeof returnFocus.focus==='function')returnFocus.focus({preventScroll:true});}catch{}});
  });
  $$('[data-close]').forEach(button=>button.addEventListener('click',()=>closeDialog(button.closest('dialog'))));
  $$('[data-modal]').forEach(button=>button.addEventListener('click',()=>{closeMobileMenu();closeLanguageMenu();openDialog(button.dataset.modal);}));

  function appendIcon(parent,name){
    const svg=document.createElementNS('http://www.w3.org/2000/svg','svg'); svg.setAttribute('class','mayhen_docs_icon');svg.setAttribute('aria-hidden','true');
    const use=document.createElementNS('http://www.w3.org/2000/svg','use');use.setAttribute('href','#mayhen_docs_i_'+name);svg.appendChild(use);parent.appendChild(svg);
  }
  function makeGuideResult(id,onClick){
    const g=GUIDES[id];const button=document.createElement('button');button.type='button';
    appendIcon(button,'book');const body=document.createElement('span');
    const title=document.createElement('strong'); title.textContent=g.title;
    const small=document.createElement('small');small.textContent=g.category+' Â· '+g.summary;
    body.append(title,small);button.append(body);appendIcon(button,'arrow');button.addEventListener('click',onClick||(()=>openGuide(id)));
    return button;
  }
  function renderSearch(){
    const container=$('#mayhen_docs_search_results');const query=$('#mayhen_docs_search_input').value;
    container.replaceChildren();const results=searchGuides(query);searchActiveIndex=0;
    if(!results.length){const empty=document.createElement('div');empty.className='mayhen_docs_no_results';empty.textContent='ChÆ°a tÃ¬m tháº¥y ná»™i dung phÃ¹ há»£p. Thá»­ tá»« khÃ³a ngáº¯n hÆ¡n hoáº·c gá»­i gÃ³p Ã½ Ä‘á»ƒ bá»• sung tÃ i liá»‡u.';container.append(empty);return;}
    results.slice(0,12).forEach((id,i)=>{
      const btn=makeGuideResult(id,()=>{closeDialog($('#mayhen_docs_dialog_search'));openGuide(id);});
      btn.setAttribute('role','option');btn.setAttribute('aria-selected',i===0?'true':'false');container.append(btn);
      btn.addEventListener('mouseenter',()=>{searchActiveIndex=i;highlightSearch(false);});
    });
  }
  function highlightSearch(scroll){
    const options=$$('#mayhen_docs_search_results>[role="option"]');
    options.forEach((button,index)=>button.setAttribute('aria-selected',index===searchActiveIndex?'true':'false'));
    if(scroll)options[searchActiveIndex]?.scrollIntoView({block:'nearest'});
  }
  $('#mayhen_docs_search_input').addEventListener('input',renderSearch);
  $('#mayhen_docs_search_input').addEventListener('keydown',event=>{
    const options=$$('#mayhen_docs_search_results>[role="option"]');
    if(event.key==='ArrowDown'){event.preventDefault();searchActiveIndex=(searchActiveIndex+1)%Math.max(1,options.length);highlightSearch(true);}
    else if(event.key==='ArrowUp'){event.preventDefault();searchActiveIndex=(searchActiveIndex-1+Math.max(1,options.length))%Math.max(1,options.length);highlightSearch(true);}
    else if(event.key==='Enter'&&options.length){event.preventDefault();options[searchActiveIndex]?.click();}
    else if(event.key==='Escape'){event.preventDefault();closeDialog($('#mayhen_docs_dialog_search'));}
  });
  $$('[data-search-term]').forEach(button=>button.addEventListener('click',()=>{$('#mayhen_docs_search_input').value=button.dataset.searchTerm;renderSearch();$('#mayhen_docs_search_input').focus();}));

  function openGuide(id){
    const g=GUIDES[id];if(!g)return;
    currentGuide=id;
    $('#mayhen_docs_guide_category').textContent=g.category.toUpperCase()+' / HÆ¯á»šNG DáºªN MáºªU';
    $('#mayhen_docs_guide_title').textContent=g.title;
    $('#mayhen_docs_guide_summary').textContent=g.summary;
    $('#mayhen_docs_guide_tip').textContent=g.tip;
    const list=$('#mayhen_docs_guide_steps');list.replaceChildren();
    g.steps.forEach(step=>{const li=document.createElement('li');li.textContent=step;list.append(li);});
    updateGuideBookmarkButton();closeMobileMenu();openDialog('mayhen_docs_dialog_guide');
  }
  $$('[data-guide]').forEach(button=>button.addEventListener('click',()=>openGuide(button.dataset.guide)));
  function updateGuideBookmarkButton(){
    const saved=bookmarks.includes(currentGuide);const button=$('#mayhen_docs_guide_bookmark');
    button.querySelector('span').textContent=saved?'ÄÃ£ lÆ°u âœ“':'LÆ°u hÆ°á»›ng dáº«n';button.setAttribute('aria-pressed',String(saved));
  }
  $('#mayhen_docs_guide_bookmark').addEventListener('click',()=>{
    if(bookmarks.includes(currentGuide)){bookmarks=bookmarks.filter(id=>id!==currentGuide);toast('ÄÃ£ bá» hÆ°á»›ng dáº«n khá»i má»¥c Ä‘Ã£ lÆ°u.');}
    else{bookmarks.unshift(currentGuide);toast('ÄÃ£ lÆ°u hÆ°á»›ng dáº«n trÃªn thiáº¿t bá»‹.');}
    store.set(BOOKMARKS_KEY,bookmarks);updateGuideBookmarkButton();
  });
  async function copyText(value){
    try {if(navigator.clipboard && window.isSecureContext){await navigator.clipboard.writeText(value);return true;}} catch {}
    let textarea;
    try{
      textarea=document.createElement('textarea');textarea.value=value;textarea.setAttribute('readonly','');textarea.style.cssText='position:fixed;left:-10000px;top:0;opacity:0';(root.querySelector('dialog[open]') || document.body).appendChild(textarea);
      textarea.select();textarea.setSelectionRange(0,textarea.value.length);return document.execCommand('copy');
    }catch{return false;}finally{textarea?.remove();}
  }
  $('#mayhen_docs_guide_copy').addEventListener('click',async()=>{
    const g=GUIDES[currentGuide];const text=[g.title,'',g.summary,'',...g.steps.map((s,i)=>`${i+1}. ${s}`),'','Máº¹o: '+g.tip].join('\n');
    toast(await copyText(text)?'ÄÃ£ sao chÃ©p ná»™i dung hÆ°á»›ng dáº«n.':'ChÆ°a sao chÃ©p Ä‘Æ°á»£c, hÃ£y thá»­ láº¡i trong trÃ¬nh duyá»‡t HTTPS.');
  });
  function renderBookmarks(){
    const list=$('#mayhen_docs_saved_list');list.replaceChildren();
    if(!bookmarks.length){const none=document.createElement('p');none.className='mayhen_docs_no_results';none.textContent='ChÆ°a cÃ³ hÆ°á»›ng dáº«n nÃ o Ä‘Æ°á»£c lÆ°u. HÃ£y má»Ÿ má»™t hÆ°á»›ng dáº«n vÃ  báº¥m â€œLÆ°u hÆ°á»›ng dáº«nâ€.';list.append(none);return;}
    bookmarks.forEach(id=>list.append(makeGuideResult(id,()=>openGuide(id))));
  }

  function makeAskResults(query){
    const results=searchGuides(query).slice(0,5);const list=$('#mayhen_docs_ask_results');list.replaceChildren();
    const heading=document.createElement('p');heading.textContent=results.length?'CÃ¡c hÆ°á»›ng dáº«n gáº§n vá»›i cÃ¢u há»i cá»§a báº¡n:':'ChÆ°a cÃ³ tÃ i liá»‡u trÃ¹ng khá»›p. Báº¡n cÃ³ thá»ƒ thá»­ cÃ¢u há»i ngáº¯n gá»n hÆ¡n hoáº·c gá»­i gÃ³p Ã½.';list.append(heading);
    results.forEach(id=>list.append(makeGuideResult(id,()=>openGuide(id))));
  }
  $('#mayhen_docs_ask_form').addEventListener('submit',event=>{
    event.preventDefault();const text=$('#mayhen_docs_ask_input').value.trim();if(!text){toast('Báº¡n hÃ£y nháº­p cÃ¢u há»i Ä‘á»ƒ tÃ¬m hÆ°á»›ng dáº«n.');$('#mayhen_docs_ask_input').focus();return;}makeAskResults(text);
  });

  function applySettings(persist=true){
    const theme=settings.theme==='system'?(systemDark?.matches?'dark':'light'):settings.theme;
    root.dataset.theme=theme;root.dataset.width=settings.width;root.dataset.motion=settings.motion?'on':'off';
    root.style.setProperty('--mayhen-docs-font-size',settings.font+'px');
    document.documentElement.style.colorScheme=theme;document.querySelector('meta[name="theme-color"]')?.setAttribute('content',theme==='dark'?'#1e1b1e':'#fffdfb');
    if(persist)store.set(SETTINGS_KEY,settings);
    updateSettingsUI();
  }
  function updateSettingsUI(){
    $$('[data-theme-option]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.themeOption===settings.theme)));
    $$('[data-width-option]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.widthOption===settings.width)));
    $('#mayhen_docs_font_range').value=settings.font;$('#mayhen_docs_font_output').textContent=settings.font+'px';
    $('#mayhen_docs_motion_toggle').checked=settings.motion;
  }
  $$('[data-theme-option]').forEach(button=>button.addEventListener('click',()=>{settings.theme=button.dataset.themeOption;applySettings();}));
  $$('[data-width-option]').forEach(button=>button.addEventListener('click',()=>{settings.width=button.dataset.widthOption;applySettings();}));
  $('#mayhen_docs_font_range').addEventListener('input',event=>{settings.font=Number(event.target.value);applySettings();});
  $('#mayhen_docs_font_minus').addEventListener('click',()=>{settings.font=Math.max(14,settings.font-1);applySettings();});
  $('#mayhen_docs_font_plus').addEventListener('click',()=>{settings.font=Math.min(20,settings.font+1);applySettings();});
  $('#mayhen_docs_motion_toggle').addEventListener('change',event=>{settings.motion=event.target.checked;applySettings();});
  $('#mayhen_docs_reset_settings').addEventListener('click',()=>{settings={theme:'light',font:16,width:'comfortable',motion:true};applySettings();toast('ÄÃ£ khÃ´i phá»¥c cÃ i Ä‘áº·t máº·c Ä‘á»‹nh.');});
  $('#mayhen_docs_float_theme').addEventListener('click',()=>{settings.theme=root.dataset.theme==='dark'?'light':'dark';applySettings();toast(root.dataset.theme==='dark'?'ÄÃ£ báº­t cháº¿ Ä‘á»™ tá»‘i.':'ÄÃ£ báº­t cháº¿ Ä‘á»™ sÃ¡ng.');});
  if(systemDark){const cb=()=>{if(settings.theme==='system')applySettings(false);};systemDark.addEventListener?.('change',cb);}
  applySettings(false);

  function openMobileMenu(){const sidebar=$('#mayhen_docs_sidebar');sidebar.classList.add('mayhen_docs_sidebar_open');$('#mayhen_docs_scrim').hidden=false;$('#mayhen_docs_menu_button').setAttribute('aria-expanded','true');$('#mayhen_docs_sidebar_close').focus();}
  function closeMobileMenu(){const sidebar=$('#mayhen_docs_sidebar');sidebar.classList.remove('mayhen_docs_sidebar_open');$('#mayhen_docs_scrim').hidden=true;$('#mayhen_docs_menu_button').setAttribute('aria-expanded','false');}
  $('#mayhen_docs_menu_button').addEventListener('click',()=>$('#mayhen_docs_sidebar').classList.contains('mayhen_docs_sidebar_open')?closeMobileMenu():openMobileMenu());
  $('#mayhen_docs_sidebar_close').addEventListener('click',closeMobileMenu);$('#mayhen_docs_scrim').addEventListener('click',closeMobileMenu);
  $$('.mayhen_docs_side_group').forEach(button=>button.addEventListener('click',()=>{
    const next=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(next));
    $('#mayhen_docs_group_'+button.dataset.group)?.classList.toggle('mayhen_docs_group_closed',!next);
  }));
  $$('.mayhen_docs_sidebar a[href^="#"]').forEach(anchor=>anchor.addEventListener('click',closeMobileMenu));
  function closeLanguageMenu(){$('#mayhen_docs_language_menu').hidden=true;$('#mayhen_docs_language_button').setAttribute('aria-expanded','false');}
  $('#mayhen_docs_language_button').addEventListener('click',()=>{
    const menu=$('#mayhen_docs_language_menu');const next=menu.hidden;menu.hidden=!next;$('#mayhen_docs_language_button').setAttribute('aria-expanded',String(next));
  });
  $$('[data-language]').forEach(button=>button.addEventListener('click',()=>{closeLanguageMenu();toast(button.dataset.language==='en'?'Báº£n tiáº¿ng Anh Ä‘ang Ä‘Æ°á»£c chuáº©n bá»‹; ná»™i dung hiá»‡n váº«n lÃ  tiáº¿ng Viá»‡t.':'Äang xem tÃ i liá»‡u tiáº¿ng Viá»‡t.');}));
  document.addEventListener('click',event=>{if(!event.target.closest('#mayhen_docs_language_button, #mayhen_docs_language_menu'))closeLanguageMenu();});

  function renderChecklist(){
    const el=$('#mayhen_docs_checklist_items');el.replaceChildren();
    CHECKLIST.forEach((task,index)=>{
      const label=document.createElement('label');const checkbox=document.createElement('input');checkbox.type='checkbox';checkbox.checked=tasks[index];
      const text=document.createElement('span');text.textContent=task;label.append(checkbox,text);
      checkbox.addEventListener('change',()=>{tasks[index]=checkbox.checked;store.set(CHECKLIST_KEY,tasks);updateChecklistProgress();});el.append(label);
    });updateChecklistProgress();
  }
  function updateChecklistProgress(){const count=tasks.filter(Boolean).length;$('#mayhen_docs_checklist_text').textContent=`${count} / ${CHECKLIST.length} cÃ´ng viá»‡c Ä‘Ã£ hoÃ n thÃ nh`;$('#mayhen_docs_checklist_bar').style.width=(100*count/CHECKLIST.length)+'%';}
  $('#mayhen_docs_checklist_reset').addEventListener('click',()=>{tasks=CHECKLIST.map(()=>false);store.set(CHECKLIST_KEY,tasks);renderChecklist();toast('ÄÃ£ Ä‘áº·t láº¡i checklist.');});

  function updateCountdown(){
    const date=weddingDate?new Date(weddingDate):null;
    if(!date || !Number.isFinite(date.getTime())){
      ['days','hours','minutes','seconds'].forEach(part=>$('#mayhen_docs_'+part).textContent='â€”');$('#mayhen_docs_countdown_note').textContent='Chá»n ngÃ y vÃ  giá» Ä‘á»ƒ báº¯t Ä‘áº§u Ä‘áº¿m ngÆ°á»£c.';return;
    }
    const diff=Math.max(0,date.getTime()-Date.now());const days=Math.floor(diff/86400000),hours=Math.floor(diff/3600000)%24,minutes=Math.floor(diff/60000)%60,seconds=Math.floor(diff/1000)%60;
    $('#mayhen_docs_days').textContent=days;$('#mayhen_docs_hours').textContent=String(hours).padStart(2,'0');$('#mayhen_docs_minutes').textContent=String(minutes).padStart(2,'0');$('#mayhen_docs_seconds').textContent=String(seconds).padStart(2,'0');
    $('#mayhen_docs_countdown_note').textContent=diff===0?'NgÃ y háº¹n Ä‘Ã£ Ä‘áº¿n hoáº·c Ä‘Ã£ qua. ChÃºc hai báº¡n tháº­t nhiá»u háº¡nh phÃºc!':'Má»—i khoáº£nh kháº¯c Ä‘ang Ä‘Æ°a hai báº¡n Ä‘áº¿n gáº§n ngÃ y Ä‘áº·c biá»‡t hÆ¡n.';
  }
  $('#mayhen_docs_date_input').value=weddingDate;
  $('#mayhen_docs_date_save').addEventListener('click',()=>{const value=$('#mayhen_docs_date_input').value;
    if(!value || !Number.isFinite(new Date(value).getTime())){toast('Vui lÃ²ng chá»n ngÃ y vÃ  giá» há»£p lá»‡.');return;}
    weddingDate=value;store.set(DATE_KEY,weddingDate);updateCountdown();toast('ÄÃ£ lÆ°u ngÃ y háº¹n trÃªn thiáº¿t bá»‹.');
  });
  window.setInterval(()=>{if($('#mayhen_docs_dialog_countdown').open)updateCountdown();},1000);

  $('#mayhen_docs_share_copy').addEventListener('click',async()=>toast(await copyText($('#mayhen_docs_share_link').value)?'ÄÃ£ sao chÃ©p liÃªn káº¿t.':'KhÃ´ng thá»ƒ sao chÃ©p, vui lÃ²ng chá»n liÃªn káº¿t vÃ  sao chÃ©p thá»§ cÃ´ng.'));
  $('#mayhen_docs_native_share').addEventListener('click',async()=>{
    if(navigator.share){try{await navigator.share({title:document.title,text:'Trung tÃ¢m hÆ°á»›ng dáº«n MÃ¢y Háº¹n',url:window.location.href});}catch(error){if(error?.name!=='AbortError')toast('Thiáº¿t bá»‹ khÃ´ng thá»ƒ chia sáº» liÃªn káº¿t nÃ y.');}}
    else toast('TrÃ¬nh duyá»‡t chÆ°a há»— trá»£ chia sáº» trá»±c tiáº¿p. Báº¡n cÃ³ thá»ƒ dÃ¹ng nÃºt Sao chÃ©p.');
  });
  $('#mayhen_docs_print').addEventListener('click',()=>{closeDialog($('#mayhen_docs_dialog_share'));window.setTimeout(()=>window.print(),80);});
  const draft=store.get(FEEDBACK_KEY,'');$('#mayhen_docs_feedback_text').value=typeof draft==='string'?draft:'';
  function updateFeedbackCount(){$('#mayhen_docs_feedback_count').textContent=$('#mayhen_docs_feedback_text').value.length+' / 1500 kÃ½ tá»±';}
  $('#mayhen_docs_feedback_text').addEventListener('input',updateFeedbackCount);updateFeedbackCount();
  $('#mayhen_docs_feedback_save').addEventListener('click',()=>{const val=$('#mayhen_docs_feedback_text').value;store.set(FEEDBACK_KEY,val);toast('ÄÃ£ lÆ°u nhÃ¡p gÃ³p Ã½ trÃªn thiáº¿t bá»‹. ChÆ°a gá»­i Ä‘áº¿n MÃ¢y Háº¹n.');});
  $('#mayhen_docs_feedback_copy').addEventListener('click',async()=>{const val=$('#mayhen_docs_feedback_text').value.trim();if(!val){toast('Vui lÃ²ng nháº­p ná»™i dung gÃ³p Ã½.');return;}const ok=await copyText('GÃ³p Ã½ cho MÃ¢y Háº¹n Docs\n\n'+val);toast(ok?'ÄÃ£ sao chÃ©p gÃ³p Ã½. Báº¡n cÃ³ thá»ƒ gá»­i tá»›i Ä‘á»™i ngÅ© MÃ¢y Háº¹n.':'KhÃ´ng thá»ƒ sao chÃ©p. Báº¡n cÃ³ thá»ƒ chá»n vÄƒn báº£n vÃ  sao chÃ©p thá»§ cÃ´ng.');});
  const rating=store.get(RATING_KEY,'');$$('[data-feedback]').forEach(button=>{
    button.setAttribute('aria-pressed',String(button.dataset.feedback===rating));
    button.addEventListener('click',()=>{store.set(RATING_KEY,button.dataset.feedback);$$('[data-feedback]').forEach(other=>other.setAttribute('aria-pressed',String(other===button)));
      if(button.dataset.feedback==='yes')toast('Cáº£m Æ¡n báº¡n Ä‘Ã£ Ä‘Ã¡nh giÃ¡. â™¡');else openDialog('mayhen_docs_dialog_feedback');
    });
  });

  // Carousel navigation, with disabled state for discoverability.
  const track=$('#mayhen_docs_workflow_track'),previous=$('#mayhen_docs_carousel_prev'),next=$('#mayhen_docs_carousel_next');
  function updateCarouselButtons(){previous.disabled=track.scrollLeft<=2;next.disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-3;}
  const carouselAmount=()=>Math.min(track.clientWidth*.84,335);
  previous.addEventListener('click',()=>track.scrollBy({left:-carouselAmount(),behavior:'smooth'}));
  next.addEventListener('click',()=>track.scrollBy({left:carouselAmount(),behavior:'smooth'}));
  track.addEventListener('scroll',updateCarouselButtons,{passive:true});window.addEventListener('resize',updateCarouselButtons);window.setTimeout(updateCarouselButtons,100);
  $$('.mayhen_docs_faq_list details').forEach(details=>details.addEventListener('toggle',()=>{if(details.open)$$('.mayhen_docs_faq_list details').filter(item=>item!==details && item.open).forEach(other=>other.open=false);}));

  const topButton=$('#mayhen_docs_to_top');
  function handleScroll(){const max=document.documentElement.scrollHeight-innerHeight;$('#mayhen_docs_progress').style.width=(max>0?Math.min(100,100*scrollY/max):0)+'%';topButton.classList.toggle('mayhen_docs_to_top_visible',scrollY>470);}
  window.addEventListener('scroll',handleScroll,{passive:true});window.addEventListener('resize',handleScroll);handleScroll();
  topButton.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));$('#mayhen_docs_footer_top').addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
  document.addEventListener('keydown',event=>{
    const editing=event.target.matches?.('input, textarea, [contenteditable="true"]');
    if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){event.preventDefault();openDialog('mayhen_docs_dialog_search');return;}
    if(event.key==='Escape'){closeMobileMenu();closeLanguageMenu();return;}
    if(!editing && !root.querySelector('dialog[open]') && event.key==='/'){event.preventDefault();openDialog('mayhen_docs_dialog_search');}
    if(!editing && !root.querySelector('dialog[open]') && event.key==='?'){event.preventDefault();openDialog('mayhen_docs_dialog_shortcuts');}
  });
  // Incoming deep-links to bundled guide summaries: file.html#guide-rsvp
  let linkedGuide='';
  try {linkedGuide=decodeURIComponent(window.location.hash.slice(1));} catch {linkedGuide='';}
  if(linkedGuide.startsWith('guide-')&&GUIDES[linkedGuide.slice(6)]){
    window.setTimeout(()=>openGuide(linkedGuide.slice(6)),140);
  }
})();
/* MÃ‚Y Háº¸N DOCS - Vanilla JavaScript, no dependencies.
 * Interactive UI demo only. No server/API, no tracking, no fake submissions.
 */
(() => {
  'use strict';
  const PREFIX = 'mayhen_docs_detail_';
  const app = document.getElementById(PREFIX + 'app');
  if (!app) return;
  const byId = suffix => document.getElementById(PREFIX + suffix);
  const all = selector => Array.from(app.querySelectorAll(selector));
  const documentSections = all('.mayhen_docs_detail_doc_section[id]');
  const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');
  const safeStorage = {
    get(key) { try { return localStorage.getItem(PREFIX + key); } catch { return null; } },
    set(key, value) { try { localStorage.setItem(PREFIX + key, String(value)); } catch { /* private mode */ } }
  };
  const state = {
    theme: safeStorage.get('theme') || 'light',
    width: safeStorage.get('width') || 'comfortable',
    font: Math.max(14, Math.min(21, Number(safeStorage.get('font')) || 16)),
    focus: safeStorage.get('focus') === 'true',
    bookmark: safeStorage.get('bookmark') === 'true',
    selectedSearchIndex: 0,
    results: [],
    pendingFrame: false,
    toastTimeout: null
  };

  function toast(message) {
    const el = byId('toast');
    el.textContent = message;
    el.classList.add(PREFIX + 'visible');
    window.clearTimeout(state.toastTimeout);
    state.toastTimeout = window.setTimeout(() => el.classList.remove(PREFIX + 'visible'), 3000);
  }

  async function copyText(text) {
    if (!text) return false;
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      }
    } catch { /* fall back for file:// or restricted browsers */ }
    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.top = '-9999px';
    document.body.append(input);
    input.select();
    let copied = false;
    try { copied = document.execCommand('copy'); } catch { /* no clipboard permission */ }
    input.remove();
    return copied;
  }
  async function copyAndToast(text, success = 'ÄÃ£ sao chÃ©p vÃ o bá»™ nhá»› táº¡m.') {
    const copied = await copyText(text);
    toast(copied ? success : 'KhÃ´ng thá»ƒ sao chÃ©p tá»± Ä‘á»™ng. HÃ£y dÃ¹ng Ctrl + C Ä‘á»ƒ sao chÃ©p.');
  }

  function smoothTop() {
    window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  }
  byId('footer_top').addEventListener('click', smoothTop);
  byId('back_top').addEventListener('click', smoothTop);

  function applyTheme() {
    const theme = state.theme === 'system' ? (themeMedia.matches ? 'dark' : 'light') : state.theme;
    app.dataset.theme = theme;
    all('[data-theme-option]').forEach(btn => btn.setAttribute('aria-pressed', String(btn.dataset.themeOption === state.theme)));
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#252123' : '#fffcfa');
  }
  function applyWidth() {
    app.dataset.width = state.width;
    all('[data-width-option]').forEach(btn => btn.setAttribute('aria-pressed', String(btn.dataset.widthOption === state.width)));
  }
  function setFont(next) {
    state.font = Math.max(14, Math.min(21, next));
    app.style.setProperty('--mayhen-docs-detail-font-size', state.font + 'px');
    byId('settings_font_value').textContent = state.font + 'px';
    safeStorage.set('font', state.font);
  }
  function setFocus(on) {
    state.focus = Boolean(on);
    app.classList.toggle(PREFIX + 'is_focus', state.focus);
    byId('focus_toggle').setAttribute('aria-pressed', String(state.focus));
    byId('focus_checkbox').checked = state.focus;
    safeStorage.set('focus', state.focus);
  }
  function applyBookmark() {
    const btn = byId('bookmark');
    btn.setAttribute('aria-pressed', String(state.bookmark));
    btn.querySelector('span').textContent = state.bookmark ? 'ÄÃ£ lÆ°u' : 'LÆ°u bÃ i';
    btn.style.color = state.bookmark ? 'var(--mayhen-docs-detail-rose)' : '';
  }
  if (!['light', 'dark', 'system'].includes(state.theme)) state.theme = 'light';
  if (!['comfortable', 'wide'].includes(state.width)) state.width = 'comfortable';
  applyTheme(); applyWidth(); setFont(state.font); setFocus(state.focus); applyBookmark();
  if (themeMedia.addEventListener) themeMedia.addEventListener('change', () => { if (state.theme === 'system') applyTheme(); });
  all('[data-theme-option]').forEach(btn => btn.addEventListener('click', () => {
    state.theme = btn.dataset.themeOption; safeStorage.set('theme', state.theme); applyTheme();
  }));
  all('[data-width-option]').forEach(btn => btn.addEventListener('click', () => {
    state.width = btn.dataset.widthOption; safeStorage.set('width', state.width); applyWidth();
  }));
  byId('theme_quick').addEventListener('click', () => {
    state.theme = app.dataset.theme === 'light' ? 'dark' : 'light';
    safeStorage.set('theme', state.theme); applyTheme();
    toast(state.theme === 'dark' ? 'ÄÃ£ chuyá»ƒn sang cháº¿ Ä‘á»™ tá»‘i.' : 'ÄÃ£ chuyá»ƒn sang cháº¿ Ä‘á»™ sÃ¡ng.');
  });
  byId('font_decrease').addEventListener('click', () => setFont(state.font - 1));
  byId('font_increase').addEventListener('click', () => setFont(state.font + 1));
  byId('settings_smaller').addEventListener('click', () => setFont(state.font - 1));
  byId('settings_bigger').addEventListener('click', () => setFont(state.font + 1));
  byId('focus_toggle').addEventListener('click', () => setFocus(!state.focus));
  byId('focus_checkbox').addEventListener('change', e => setFocus(e.target.checked));
  byId('bookmark').addEventListener('click', () => {
    state.bookmark = !state.bookmark;
    safeStorage.set('bookmark', state.bookmark);
    applyBookmark(); toast(state.bookmark ? 'ÄÃ£ lÆ°u bÃ i viáº¿t trÃªn thiáº¿t bá»‹ nÃ y.' : 'ÄÃ£ bá» lÆ°u bÃ i viáº¿t.');
  });

  // Sidebar collapsible groups and mobile drawer.
  function toggleDrawer(open) {
    app.classList.toggle(PREFIX + 'mobile_open', open);
    byId('menu_open').setAttribute('aria-expanded', String(open));
    document.documentElement.style.overflow = open ? 'hidden' : '';
    if (open) byId('menu_close').focus();
  }
  byId('menu_open').addEventListener('click', () => toggleDrawer(true));
  byId('menu_close').addEventListener('click', () => toggleDrawer(false));
  byId('mobile_scrim').addEventListener('click', () => toggleDrawer(false));
  byId('sidebar_toggle').addEventListener('click', () => {
    app.classList.toggle(PREFIX + 'is_sidebar_collapsed');
    const collapsed = app.classList.contains(PREFIX + 'is_sidebar_collapsed');
    byId('sidebar_toggle').setAttribute('aria-pressed', String(collapsed));
    byId('sidebar_toggle').setAttribute('title', collapsed ? 'Má»Ÿ thanh Ä‘iá»u hÆ°á»›ng' : 'Thu gá»n thanh Ä‘iá»u hÆ°á»›ng');
  });
  all('.mayhen_docs_detail_nav_group_toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', String(expanded));
      document.getElementById(btn.getAttribute('aria-controls')).hidden = !expanded;
    });
  });
  all('.mayhen_docs_detail_sidebar a[href^="#"]').forEach(link => link.addEventListener('click', () => toggleDrawer(false)));
  window.addEventListener('resize', () => { if (window.innerWidth > 1020) toggleDrawer(false); }, { passive: true });

  // Native accessible dialog management.
  const dialogs = all('dialog');
  function openDialog(id) {
    const dialog = document.getElementById(id);
    if (!dialog || !(dialog instanceof HTMLDialogElement)) return;
    dialogs.forEach(d => { if (d.open) d.close(); });
    dialog.showModal();
    document.body.style.overflow = 'hidden';
    if (id === PREFIX + 'search_dialog') {
      byId('search_input').value = '';
      renderSearch('');
      requestAnimationFrame(() => byId('search_input').focus());
    }
    if (id === PREFIX + 'share_dialog') byId('share_input').value = window.location.href;
  }
  function closeDialog(dialog) {
    if (dialog && dialog.open) dialog.close();
  }
  all('[data-modal]').forEach(btn => btn.addEventListener('click', () => openDialog(btn.dataset.modal)));
  all('[data-close-dialog]').forEach(btn => btn.addEventListener('click', () => closeDialog(btn.closest('dialog'))));
  dialogs.forEach(dialog => {
    dialog.addEventListener('close', () => { if (!dialogs.some(d => d.open)) document.body.style.overflow = ''; });
    dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
  });

  // Build accessible TOC modal from the same links as the right rail.
  const tocLinks = all('#mayhen_docs_detail_toc_links a[href^="#"]');
  byId('toc_modal_links').innerHTML = tocLinks.map(link => `<a href="${link.getAttribute('href')}">${link.textContent}</a>`).join('');
  all('#mayhen_docs_detail_toc_modal_links a').forEach(link => link.addEventListener('click', () => closeDialog(byId('toc_dialog'))));

  // Scroll spy and reading progress.
  function updateScrollUI() {
    state.pendingFrame = false;
    const article = byId('article');
    const absoluteTop = article.getBoundingClientRect().top + scrollY;
    const absoluteBottom = article.getBoundingClientRect().bottom + scrollY;
    const denominator = Math.max(1, absoluteBottom - absoluteTop - window.innerHeight + 120);
    const percent = Math.max(0, Math.min(100, Math.round(((window.scrollY - absoluteTop + 40) / denominator) * 100)));
    byId('reading_progress').style.width = percent + '%';
    byId('toc_progress_bar').style.width = percent + '%';
    byId('read_percent').textContent = percent + '%';
    byId('back_top').classList.toggle(PREFIX + 'visible', window.scrollY > 440);
    let active = documentSections[0]?.id;
    for (const section of documentSections) {
      if (section.getBoundingClientRect().top <= 205) active = section.id;
      else break;
    }
    tocLinks.forEach(a => {
      if (a.getAttribute('href') === '#' + active) a.setAttribute('aria-current', 'location');
      else a.removeAttribute('aria-current');
    });
    all('.mayhen_docs_detail_nav_link[href^="#"]').forEach(a => {
      const isActive = window.scrollY < 260
        ? a.getAttribute('href') === '#' + PREFIX + 'article'
        : a.getAttribute('href') === '#' + active;
      a.classList.toggle(PREFIX + 'nav_active', isActive);
    });
  }
  function scheduleScrollUpdate() {
    if (state.pendingFrame) return;
    state.pendingFrame = true;
    requestAnimationFrame(updateScrollUI);
  }
  window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
  window.addEventListener('resize', scheduleScrollUpdate, { passive: true });
  updateScrollUI();

  // Search is local to this demo page; results link to real sections here.
  const searchIndex = documentSections.map((section, order) => ({
    id: section.id,
    title: section.querySelector('h2')?.textContent.trim() || 'HÆ°á»›ng dáº«n',
    summary: (section.querySelector('p')?.textContent.trim() || '').slice(0, 125),
    content: section.textContent.trim().slice(0, 2500),
    order
  }));
  function normalize(text) {
    return (text || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/Ä‘/g, 'd').replace(/\s+/g, ' ').trim();
  }
  function renderSearch(query) {
    const words = normalize(query).split(/\s+/).filter(Boolean);
    const results = searchIndex.map(item => {
      const title = normalize(item.title);
      const summary = normalize(item.summary);
      const content = normalize(item.content);
      const matches = words.every(word => title.includes(word) || summary.includes(word) || content.includes(word));
      const score = words.reduce((sum, word) => sum + (title.includes(word) ? 5 : 0) + (summary.includes(word) ? 2 : 0) + (content.includes(word) ? 1 : 0), 0);
      return { ...item, matches, score };
    }).filter(item => words.length ? item.matches : true)
      .sort((a, b) => words.length ? b.score - a.score || a.order - b.order : a.order - b.order);
    state.results = results;
    state.selectedSearchIndex = 0;
    byId('search_count').textContent = words.length ? `${results.length} káº¿t quáº£ phÃ¹ há»£p` : 'CÃ¡c má»¥c hÆ°á»›ng dáº«n gá»£i Ã½';
    const list = byId('search_results');
    list.replaceChildren();
    if (!results.length) {
      const empty = document.createElement('div');
      empty.className = PREFIX + 'search_empty';
      empty.textContent = 'ChÆ°a tÃ¬m tháº¥y má»¥c phÃ¹ há»£p. HÃ£y thá»­ má»™t tá»« khÃ³a ngáº¯n hÆ¡n.';
      list.append(empty);
      return;
    }
    results.forEach((result, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = PREFIX + 'search_result';
      btn.dataset.resultIndex = String(index);
      btn.setAttribute('aria-selected', String(index === 0));
      const symbol = document.createElement('span'); symbol.className = PREFIX + 'search_result_icon'; symbol.textContent = 'âœ§';
      const text = document.createElement('span'); text.className = PREFIX + 'search_result_text';
      const title = document.createElement('strong'); title.textContent = result.title;
      const description = document.createElement('small'); description.textContent = result.summary || 'Äi tá»›i pháº§n hÆ°á»›ng dáº«n';
      text.append(title, description);
      const arrow = document.createElement('span'); arrow.className = PREFIX + 'search_result_arrow'; arrow.textContent = 'â†—';
      btn.append(symbol, text, arrow);
      btn.addEventListener('mouseenter', () => selectSearchResult(index));
      btn.addEventListener('click', () => goToSearchResult(index));
      list.append(btn);
    });
  }
  function selectSearchResult(index) {
    if (!state.results.length) return;
    state.selectedSearchIndex = (index + state.results.length) % state.results.length;
    all('.mayhen_docs_detail_search_result').forEach(btn => btn.setAttribute('aria-selected', String(Number(btn.dataset.resultIndex) === state.selectedSearchIndex)));
    all('.mayhen_docs_detail_search_result')[state.selectedSearchIndex]?.scrollIntoView({ block: 'nearest' });
  }
  function goToSearchResult(index) {
    const item = state.results[index];
    if (!item) return;
    closeDialog(byId('search_dialog'));
    const element = document.getElementById(item.id);
    if (!element) return;
    history.replaceState(null, '', '#' + item.id);
    element.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
  }
  byId('search_input').addEventListener('input', e => renderSearch(e.target.value));
  byId('search_input').addEventListener('keydown', e => {
    if (e.key === 'ArrowDown') { e.preventDefault(); selectSearchResult(state.selectedSearchIndex + 1); }
    if (e.key === 'ArrowUp') { e.preventDefault(); selectSearchResult(state.selectedSearchIndex - 1); }
    if (e.key === 'Enter') { e.preventDefault(); goToSearchResult(state.selectedSearchIndex); }
  });
  renderSearch('');
  document.addEventListener('keydown', e => {
    // Escape first closes a dialog, even when a search input normally consumes Escape.
    if (e.key === 'Escape') {
      const activeDialog = dialogs.find(dialog => dialog.open);
      if (activeDialog) { e.preventDefault(); closeDialog(activeDialog); return; }
    }
    const isTyping = e.target instanceof HTMLElement && (e.target.matches('input,textarea,select') || e.target.isContentEditable);
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openDialog(PREFIX + 'search_dialog'); return; }
    if (e.key === 'Escape' && app.classList.contains(PREFIX + 'mobile_open')) { toggleDrawer(false); return; }
    if (isTyping || dialogs.some(dialog => dialog.open) || e.ctrlKey || e.altKey || e.metaKey) return;
    if (e.key === '/') { e.preventDefault(); openDialog(PREFIX + 'search_dialog'); }
    if (e.key === '?') { e.preventDefault(); openDialog(PREFIX + 'shortcuts_dialog'); }
  });

  // Copy Markdown from readable content, not the UI's control labels.
  function articleToMarkdown() {
    const title = byId('article').querySelector('h1').textContent.replace(/\s+/g, ' ').trim();
    const lead = byId('article').querySelector('.mayhen_docs_detail_lead').textContent.trim();
    const lines = [`# ${title}`, '', lead, ''];
    documentSections.forEach(section => {
      lines.push('## ' + section.querySelector('h2').textContent.trim(), '');
      Array.from(section.children).forEach(child => {
        if (child.matches('p')) lines.push(child.textContent.trim(), '');
        if (child.matches('ul')) {
          child.querySelectorAll(':scope > li').forEach(li => lines.push('- ' + li.textContent.trim()));
          lines.push('');
        }
        if (child.matches('ol')) {
          child.querySelectorAll(':scope > li').forEach((li, index) => lines.push(`${index + 1}. ${li.textContent.trim()}`));
          lines.push('');
        }
        if (child.matches('.mayhen_docs_detail_callout')) lines.push('> ' + child.textContent.trim().replace(/\s+/g, ' '), '');
        if (child.matches('.mayhen_docs_detail_quote')) lines.push('> ' + child.querySelector('p').textContent.trim(), '');
        if (child.matches('.mayhen_docs_detail_table_wrap')) {
          const rows = Array.from(child.querySelectorAll('tr')).map(row => Array.from(row.children).map(cell => cell.textContent.trim().replace(/\|/g, '\\|')));
          rows.forEach((row, i) => { lines.push('| ' + row.join(' | ') + ' |'); if (!i) lines.push('| ' + row.map(() => '---').join(' | ') + ' |'); });
          lines.push('');
        }
      });
    });
    return lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
  }
  byId('copy_markdown').addEventListener('click', () => copyAndToast(articleToMarkdown(), 'ÄÃ£ sao chÃ©p ná»™i dung Markdown.'));
  byId('copy_invitation').addEventListener('click', () => copyAndToast(byId('invitation_text').textContent.trim(), 'ÄÃ£ sao chÃ©p lá»i má»i máº«u.'));
  byId('copy_example_url').addEventListener('click', () => copyAndToast(byId('example_url').textContent.trim(), 'ÄÃ£ sao chÃ©p Ä‘Æ°á»ng dáº«n vÃ­ dá»¥.'));
  all('[data-copy-section]').forEach(btn => btn.addEventListener('click', () => {
    const url = new URL(location.href); url.hash = btn.dataset.copySection;
    copyAndToast(url.href, 'ÄÃ£ sao chÃ©p liÃªn káº¿t tá»›i má»¥c nÃ y.');
  }));
  byId('copy_share').addEventListener('click', () => copyAndToast(byId('share_input').value, 'ÄÃ£ sao chÃ©p Ä‘Æ°á»ng dáº«n bÃ i viáº¿t.'));
  byId('native_share').addEventListener('click', async () => {
    if (!navigator.share) {
      await copyAndToast(location.href, 'Thiáº¿t bá»‹ khÃ´ng há»— trá»£ chia sáº» trá»±c tiáº¿p. ÄÃ£ sao chÃ©p Ä‘Æ°á»ng dáº«n.');
      return;
    }
    try { await navigator.share({ title: document.title, text: 'CÃ¹ng khÃ¡m phÃ¡ hÆ°á»›ng dáº«n MÃ¢y Háº¹n', url: location.href }); }
    catch (error) { if (error?.name !== 'AbortError') toast('ChÆ°a thá»ƒ chia sáº» trÃªn thiáº¿t bá»‹ nÃ y.'); }
  });
  byId('print').addEventListener('click', () => { closeDialog(byId('share_dialog')); window.setTimeout(() => window.print(), 80); });

  // Helpful votes are stored only on this device. Feedback is explicitly copied, never faked as sent.
  const savedVote = safeStorage.get('helpful');
  all('[data-feedback-vote]').forEach(btn => {
    btn.setAttribute('aria-pressed', String(btn.dataset.feedbackVote === savedVote));
    btn.addEventListener('click', () => {
      safeStorage.set('helpful', btn.dataset.feedbackVote);
      all('[data-feedback-vote]').forEach(v => v.setAttribute('aria-pressed', String(v === btn)));
      if (btn.dataset.feedbackVote === 'no') {
        byId('feedback_type').value = 'Ná»™i dung khÃ³ hiá»ƒu';
        openDialog(PREFIX + 'feedback_dialog');
      } else toast('Cáº£m Æ¡n báº¡n! ÄÃ¡nh giÃ¡ Ä‘Ã£ Ä‘Æ°á»£c lÆ°u trÃªn thiáº¿t bá»‹ nÃ y.');
    });
  });
  byId('feedback_form').addEventListener('submit', async e => {
    e.preventDefault();
    const message = byId('feedback_message').value.trim();
    if (message.length < 5) { toast('Báº¡n hÃ£y viáº¿t Ã­t nháº¥t 5 kÃ½ tá»± nhÃ©.'); byId('feedback_message').focus(); return; }
    const feedback = `GÃ³p Ã½ tÃ i liá»‡u MÃ¢y Háº¹n\nChá»§ Ä‘á»: ${byId('feedback_type').value}\nTrang: ${location.href}\n\n${message}`;
    const copied = await copyText(feedback);
    if (copied) {
      closeDialog(byId('feedback_dialog'));
      toast('ÄÃ£ sao chÃ©p gÃ³p Ã½. HÃ£y gá»­i qua kÃªnh há»— trá»£ chÃ­nh thá»©c.');
    } else toast('KhÃ´ng thá»ƒ sao chÃ©p tá»± Ä‘á»™ng. Vui lÃ²ng chá»n ná»™i dung vÃ  sao chÃ©p thá»§ cÃ´ng.');
  });
})();
