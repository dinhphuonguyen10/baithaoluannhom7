    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column support">
                <h3>HỖ TRỢ KHÁCH HÀNG</h3>
                <p class="hotline">Hotline: <span>1800 6324</span></p>
                <p class="hotline-note">(Miễn phí, 08-22h kể cả T7, CN)</p>
                <ul>
                    <li>Các câu hỏi thường gặp</li>
                    <li>Hướng dẫn đặt hàng</li>
                    <li>Phương thức thanh toán</li>
                    <li>Phương thức vận chuyển</li>
                    <li>Chính sách đổi trả</li>
                </ul>
            </div>
            <div class="footer-column about">
                <h3>VỀ ÉCRIN JEWELRY</h3>
                <ul>
                    <li>Giới thiệu Écrin Jewelry</li>
                    <li>Chính sách bảo mật</li>
                    <li>Điều khoản sử dụng</li>
                    <li>Tuyển dụng</li>
                    <li>Liên hệ</li>
                    <li id="admin-footer-link" style="display: none;"><a href="<?php echo htmlspecialchars($appBaseUrl . '/admin/products.php', ENT_QUOTES, 'UTF-8'); ?>"
                            style="color: var(--gold); font-weight: bold;">Quản trị hệ thống</a></li>
                </ul>
            </div>
            <div class="footer-column partners">
                <h3>HỢP TÁC & LIÊN KẾT</h3>
                <ul>
                    <li>Écrin Jewelry</li>
                    <li>Trang sức Écrin</li>
                </ul>
            </div>
            <div class="footer-column subscribe">
                <h3>CẬP NHẬT THÔNG TIN KHUYẾN MÃI</h3>
                <div class="subscribe-box">
                    <input type="email" placeholder="Email của bạn">
                    <button>Đăng ký</button>
                </div>
            </div>
        </div>
        <div class="footer-note">
            <p>© 2026 ÉCRIN JEWELRY. Công Ty TNHH Thương Mại Và Đầu Tư Xuất Nhập Khẩu Việt Nam. MST: 0107837344</p>
        </div>
    </footer>

    <!-- MODAL ĐĂNG NHẬP -->
    <div id="login-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-modal" id="close-login">&times;</span>
            <h2 style="margin-bottom: 8px; color: var(--navy); text-align: center;">Đăng nhập khách hàng</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" id="login-username" placeholder="Tên đăng nhập"
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <input type="password" id="login-password" placeholder="Mật khẩu"
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <button id="submit-login"
                    style="padding: 12px; background: linear-gradient(135deg, var(--navy), var(--navy-mid)); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Đăng nhập</button>
            </div>
            <p id="login-message" style="margin: 8px 0 0; text-align: center; font-size: 0.9rem; min-height: 20px; color: #c0392b;"></p>
            <p style="margin: 6px 0 0; text-align: center;">
                <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/quen_mat_khau.php', ENT_QUOTES, 'UTF-8'); ?>" style="color: var(--gold); font-weight: bold; text-decoration: none;">Quên mật khẩu?</a>
            </p>
            <p style="margin-top: 15px; text-align: center; font-size: 0.9rem; color: #666;">
                Chưa có tài khoản? <span class="register-trigger"
                    style="color: var(--gold); cursor: pointer; font-weight: bold;">Đăng ký ngay</span>
            </p>
        </div>
    </div>

    <!-- MODAL ĐĂNG KÝ -->
    <div id="register-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-modal" id="close-register">&times;</span>
            <h2 style="margin-bottom: 20px; color: var(--navy); text-align: center;">Đăng ký tài khoản</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" id="register-username" placeholder="Tên tài khoản" autocomplete="username" maxlength="100" required
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <input type="tel" id="register-phone" placeholder="Số điện thoại (10 số, dùng để lấy lại mật khẩu)" autocomplete="tel" maxlength="10" required
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <input type="password" id="register-password" placeholder="Tạo mật khẩu (ví dụ: Abc@1234)" autocomplete="new-password" minlength="8" maxlength="8" required
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <small style="font-size: 0.78rem; color: #666; margin-top: -8px; margin-bottom: 2px; line-height: 1.3;">* Yêu cầu đúng 8 ký tự: gồm chữ hoa, thường, số và ký tự đặc biệt</small>
                <input type="password" id="register-confirm-password" placeholder="Nhập lại mật khẩu" autocomplete="new-password" minlength="8" maxlength="8" required
                    style="padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <button id="submit-register"
                    style="padding: 12px; background: #fff; color: var(--navy); border: 2px solid var(--gold); border-radius: 8px; font-weight: bold; cursor: pointer;">Đăng ký</button>
            </div>
            <p id="register-message" style="margin: 8px 0 0; text-align: center; font-size: 0.9rem; min-height: 20px; color: #c0392b;"></p>
            <p style="margin-top: 10px; text-align: center; font-size: 0.9rem; color: #666;">
                Đã có tài khoản? <span class="login-trigger"
                    style="color: var(--gold); cursor: pointer; font-weight: bold;">Đăng nhập</span>
            </p>
        </div>
    </div>

    <script>window.APP_BASE_URL = <?php echo json_encode($appBaseUrl, JSON_UNESCAPED_SLASHES); ?>;</script>
    <script src="<?php echo htmlspecialchars($appBaseUrl . '/assets/script.js?v=' . filemtime(__DIR__ . '/../assets/script.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>

</html>
