/**
 * MAYHEN — dữ liệu demo. THAY THẾ toàn bộ thông tin tại đây trước khi xuất bản.
 * Không có dữ liệu cá nhân của cặp đôi trên link đích được xác minh.
 * Ảnh minh họa SVG đi kèm có thể thay bằng .jpg / .webp tùy bạn.
 */
window.MAYHEN_INVITATION_KSNVO949DUDF_CONFIG = {
  groom: 'Hoàng Nam',
  bride: 'Minh Anh',
  guestName: 'Anh Nam',
  // Sử dụng ISO 8601 có offset +07:00 để đếm ngược đúng múi giờ Việt Nam.
  weddingDate: '2027-01-16T10:00:00+07:00',
  family: {
    groomFather: '[Tên bố chú rể]',
    groomMother: '[Tên mẹ chú rể]',
    brideFather: '[Tên bố cô dâu]',
    brideMother: '[Tên mẹ cô dâu]'
  },
  events: {
    ceremony: {
      time: '10:00',
      venue: '[Địa điểm làm lễ]',
      address: '[Địa chỉ làm lễ — cập nhật trong config.js]',
      mapUrl: ''
    },
    party: {
      time: '11:30',
      venue: '[Địa điểm tiệc cưới]',
      address: '[Địa chỉ tiệc cưới — cập nhật trong config.js]',
      mapUrl: ''
    }
  },
  schedule: [
    { time: '10:00', label: 'Đón tiếp khách mời' },
    { time: '10:30', label: 'Cử hành hôn lễ' },
    { time: '11:00', label: 'Chụp ảnh lưu niệm' },
    { time: '11:30', label: 'Khai tiệc' }
  ],
  images: {
    hero: 'assets/images/hero-art.svg',
    groom: 'assets/images/groom-art.svg',
    bride: 'assets/images/bride-art.svg',
    saveTheDate: 'assets/images/scene-art.svg',
    gallery: [
      'assets/images/gallery-01.svg',
      'assets/images/gallery-02.svg',
      'assets/images/gallery-03.svg',
      'assets/images/gallery-04.svg',
      'assets/images/gallery-05.svg',
      'assets/images/gallery-06.svg'
    ]
  },
  // Nếu muốn bật nhạc: đặt file MP3 vào assets/music rồi cấu hình đường dẫn.
  musicUrl: '',
  // Để trống nếu chưa có thông tin ngân hàng. KHÔNG dùng số tài khoản giả khi xuất bản.
  bankAccounts: [
    // { label: 'Nhà trai', bank: 'Tên ngân hàng', holder: 'CHU TAI KHOAN', number: '123456789' }
  ]
};
