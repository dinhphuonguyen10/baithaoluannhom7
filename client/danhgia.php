
<div class="product-reviews-section">
    <h3>Đánh giá sản phẩm</h3>
    <div class="average-rating">
        <!-- Hiển thị số điểm sao trung bình lấy từ database -->
        <span>4.5 / 5.0 ⭐⭐⭐⭐⭐</span>
    </div>
    
    <!-- Danh sách các bình luận đánh giá của khách khác -->
    <div class="list-reviews">
        <!-- Dùng vòng lặp PHP fetch các đánh giá từ bảng reviews -->
    </div>

    <!-- Form viết đánh giá (chỉ hiện khi khách đã đăng nhập) -->
    <form action="add-review.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <label>Chấm điểm:</label>
        <select name="rating">
            <option value="5">5 Sao (Tuyệt vời)</option>
            <option value="4">4 Sao (Tốt)</option>
            <option value="3">3 Sao (Bình thường)</option>
            <option value="2">2 Sao (Tệ)</option>
            <option value="1">1 Sao (Rất tệ)</option>
        </select>
        <textarea name="comment" placeholder="Viết nhận xét của bạn về sản phẩm..." required></textarea>
        <button type="submit">Gửi đánh giá</button>
    </form>
</div>