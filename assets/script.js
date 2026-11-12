const getAppBaseUrl = () => {
    if (typeof window.APP_BASE_URL === 'string' && window.APP_BASE_URL !== '') return window.APP_BASE_URL;
    const match = window.location.pathname.match(/^(\/[^\/]+)/);
    return match ? match[1] : '';
};
const appUrl = path => {
    const base = getAppBaseUrl();
    const cleanPath = path.startsWith('/') ? path : `/${path}`;
    return `${base}${cleanPath}`;
};
const getProductImageUrl = rawPath => {
    if (!rawPath || typeof rawPath !== 'string') return appUrl('/assets/banner/logo.png');
    const trimmed = rawPath.trim();
    if (trimmed.startsWith('/') || /^https?:\/\//i.test(trimmed)) return trimmed;
    const clean = trimmed.replace(/^(?:\.\.\/)+/, '');
    return appUrl(clean);
};
// ==================== SLIDESHOW ====================
function initSlideshow() {
    const slides = document.querySelectorAll('.slideshow .slide');
    if (slides.length < 2) return;

    const slideshow = document.querySelector('.banner-left');
    const dots = slideshow?.querySelectorAll('.banner-dots .dot') ?? [];
    const previousButton = slideshow?.querySelector('.banner-arrow.prev');
    const nextButton = slideshow?.querySelector('.banner-arrow.next');
    let currentSlide = 0;
    let timer;

    const showSlide = index => {
        slides[currentSlide].classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === currentSlide;
            dot.classList.toggle('active', isActive);
            if (isActive) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    };

    const startTimer = () => {
        window.clearInterval(timer);
        timer = window.setInterval(() => showSlide(currentSlide + 1), 6000);
    };

    previousButton?.addEventListener('click', () => {
        showSlide(currentSlide - 1);
        startTimer();
    });
    nextButton?.addEventListener('click', () => {
        showSlide(currentSlide + 1);
        startTimer();
    });
    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        showSlide(index);
        startTimer();
    }));
    slideshow?.addEventListener('mouseenter', () => window.clearInterval(timer));
    slideshow?.addEventListener('mouseleave', startTimer);
    startTimer();
}

// ==================== COMBO SLIDER & DATA ====================
// Data is dynamically fetched from the database

function createSliderItems(container, products) {
    products.forEach(product => {
        const giftHTML = (product.gift && typeof product.gift === 'string' && product.gift.trim() !== '') ? `<p class="product-gift">${product.gift}</p>` : '';
        const discountHTML = product.discount ? `<div class="product-discount">${product.discount}</div>` : '';
        const imagePath = typeof product.image === 'string' ? product.image : '';
        const imageUrl = getProductImageUrl(imagePath);

        const stock = Number(product.stock) || 0;
        const soldCount = Number(product.soldCount) || 0;

        const stockHTML = stock > 0
            ? `<span class="product-stock" style="font-size: 0.82rem; color: #27ae60;">Kho: <strong>${stock}</strong></span>`
            : `<span class="product-stock" style="font-size: 0.82rem; color: #e74c3c; font-weight: 600;">Hết hàng</span>`;

        // Chỉ hiển thị lượt bán khi có đơn hàng (soldCount > 0)
        const soldHTML = soldCount > 0
            ? `<span class="sold" style="font-size: 0.82rem; color: #e67e22; font-weight: 500;">Đã bán ${soldCount}</span>`
            : '';

        const metaHTML = `
            <div class="product-meta" style="display: flex; justify-content: space-between; align-items: center; margin: 6px 0 8px;">
                ${stockHTML}
                ${soldHTML}
            </div>
        `;

        const itemHTML = `
            <div class="product-card" data-db-id="${product.id || ''}" data-category="${product.category || ''}" data-detail-url="${appUrl('/client/product.php')}?id=${encodeURIComponent(product.id || '')}" role="link" tabindex="0" aria-label="Xem chi tiết sản phẩm" style="flex-shrink: 0; width: 240px;">
                ${discountHTML}
                <button class="wishlist-btn" title="Yêu thích">♡</button>
                <img src="${imageUrl}" alt="${product.name}" class="product-image" onerror="this.src='${appUrl('/assets/banner/logo.png')}';">
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <div class="product-price">
                        <span class="price-sale">${product.priceSale}<sup>đ</sup></span>
                        ${product.priceOriginal && parsePrice(product.priceOriginal) > 0 ? `<span class="price-original">${product.priceOriginal}<sup>đ</sup></span>` : ''}
                    </div>
                    ${metaHTML}
                    ${giftHTML}
                    <button class="add-to-cart"${stock <= 0 ? ' disabled style="background:#bbb;cursor:not-allowed;"' : ''}>${stock <= 0 ? 'Tạm hết hàng' : 'Thêm vào giỏ'}</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHTML);
    });
}

function initProductSlider(containerId, prevBtnId, nextBtnId, initialData, moreData) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const prevBtn = document.getElementById(prevBtnId);
    const nextBtn = document.getElementById(nextBtnId);
    if (container.dataset.loadError === 'true') {
        container.innerHTML = '<p class="home-empty-state">Chưa kết nối được danh mục sản phẩm. Vui lòng thử lại sau.</p>';
        if (prevBtn) prevBtn.hidden = true;
        if (nextBtn) nextBtn.hidden = true;
        return;
    }

    container.innerHTML = '';
    if (initialData.length === 0) {
        container.innerHTML = '<p class="home-empty-state">Sản phẩm đang được cập nhật. Mời bạn quay lại sau nhé.</p>';
        if (prevBtn) prevBtn.hidden = true;
        if (nextBtn) nextBtn.hidden = true;
        return;
    }
    createSliderItems(container, initialData);

    const comboItems = container;

    if (!prevBtn || !nextBtn) return;

    prevBtn.addEventListener('click', () => {
        comboItems.scrollBy({
            left: -256,
            behavior: 'smooth'
        });
    });

    let hasLoadedMore = false;
    nextBtn.addEventListener('click', () => {
        if (!hasLoadedMore) {
            createSliderItems(container, moreData);
            hasLoadedMore = true;

            nextBtn.innerHTML = '❯';
            nextBtn.style.width = '40px';
            nextBtn.style.padding = '0';
            nextBtn.style.borderRadius = '50%';
            nextBtn.style.fontSize = '1.2rem';
            nextBtn.style.fontWeight = 'normal';

            // Nút Thêm vào giỏ đã được xử lý qua Event Delegation trong initAddToCart()
        }

        setTimeout(() => {
            comboItems.scrollBy({
                left: 256,
                behavior: 'smooth'
            });
        }, 10);
    });
}

async function fetchProducts(category) {
    try {
        const response = await fetch(`${appUrl('/api/catalog/get_products.php')}?category=${encodeURIComponent(category)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        // Map database fields to JS field names
        return data.map(p => ({
            id: p.id,
            name: p.name,
            discount: p.discount,
            image: p.image,
            priceSale: p.price_sale,
            priceOriginal: p.price_original,
            gift: p.gift,
            stock: Number(p.stock) || 0,
            soldCount: Number(p.sold_count) || 0,
            category: p.category
        }));
    } catch (error) {
        console.error(`Lỗi khi tải sản phẩm danh mục ${category}:`, error);
        const containerIds = {
            combo: 'combo-items-container',
            suggest: 'suggest-items-container',
            featured: 'featured-items-container'
        };
        const container = document.getElementById(containerIds[category]);
        if (container) {
            container.dataset.loadError = 'true';
        }
        return [];
    }
}

async function initComboSlider() {
    // Tải dữ liệu từ Database thay vì dùng mảng cứng
    const comboData = await fetchProducts('combo');
    const suggestData = await fetchProducts('suggest');
    const featuredData = await fetchProducts('featured');

    // Khởi tạo các slider với dữ liệu từ DB
    initProductSlider('combo-items-container', 'combo-prev', 'combo-next', comboData, []);
    initProductSlider('suggest-items-container', 'suggest-prev', 'suggest-next', suggestData, []);
    initProductSlider('featured-items-container', 'featured-prev', 'featured-next', featuredData, []);
}

// ==================== SCROLL TO TOP ====================
function initScrollToTop() {
    const scrollBtn = document.querySelector('.scroll-to-top');

    if (!scrollBtn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollBtn.classList.add('show');
        } else {
            scrollBtn.classList.remove('show');
        }
    });

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ==================== ADD TO CART ====================
let cart = [];

function getCartKey() {
    const userJson = localStorage.getItem('currentUser');
    if (userJson) {
        try {
            const user = JSON.parse(userJson);
            if (user && user.username) {
                return 'cart_' + user.username;
            }
        } catch (e) {
            console.error('Error parsing currentUser:', e);
        }
    }
    return 'cart_guest';
}

function loadCart() {
    try {
        const key = getCartKey();
        cart = JSON.parse(localStorage.getItem(key)) || [];
    } catch (e) {
        cart = [];
    }
}

function saveCart() {
    try {
        const key = getCartKey();
        localStorage.setItem(key, JSON.stringify(cart));
    } catch (e) {
        console.error('Error saving cart:', e);
    }
}

// Gọi loadCart ngay lập tức
loadCart();

function parsePrice(priceText) {
    return Number(priceText.replace(/[^\d]/g, '')) || 0;
}

function formatPrice(value) {
    return value.toLocaleString('vi-VN') + ' đ';
}

function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    cartCountElements.forEach(el => {
        el.textContent = totalItems;
        el.classList.toggle('has-items', totalItems > 0);
    });
}

function renderCart() {
    const cartItemsContainer = document.querySelector('.cart-items');
    const cartTotalPrice = document.querySelector('.cart-total-price');
    const cartTotalCount = document.querySelector('.cart-total-count');

    if (!cartItemsContainer || !cartTotalPrice || !cartTotalCount) return;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart">Giỏ hàng đang trống. Thêm sản phẩm nào đó nhé!</p>';
        cartTotalPrice.textContent = '0 đ';
        cartTotalCount.textContent = '0 sản phẩm';
        return;
    }

    cartItemsContainer.innerHTML = '';
    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);

    cart.forEach((item, index) => {
        const itemMarkup = `
            <div class="cart-item" data-index="${index}">
                <img src="${getProductImageUrl(item.image)}" alt="${item.name}" class="cart-item-image" onerror="this.src='${appUrl('/assets/banner/logo.png')}';">
                <div class="cart-item-details">
                    <p class="cart-item-name">${item.name}</p>
                    <div class="cart-item-actions">
                        <div class="cart-qty-controls">
                            <button class="qty-minus" data-action="decrease">−</button>
                            <span class="cart-qty">${item.quantity}</span>
                            <button class="qty-plus" data-action="increase">+</button>
                        </div>
                        <button class="remove-item-btn" data-action="remove">Xoá</button>
                    </div>
                    <p class="cart-item-price">${formatPrice(item.price * item.quantity)}</p>
                </div>
            </div>
        `;
        cartItemsContainer.insertAdjacentHTML('beforeend', itemMarkup);
    });

    cartTotalPrice.textContent = formatPrice(totalPrice);
    cartTotalCount.textContent = `${totalQty} sản phẩm`;
}

function addItemToCart(product) {
    loadCart(); // Đồng bộ dữ liệu mới nhất

    if (!product || !product.name) {
        console.error('Sản phẩm không hợp lệ:', product);
        return;
    }

    const existingProduct = cart.find(item =>
        product.product_id
            ? Number(item.product_id) === Number(product.product_id) && item.name === product.name
            : item.name === product.name
    );

    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        cart.push({
            product_id: product.product_id || null,
            name: product.name,
            price: product.price || 0,
            image: product.image || 'assets/banner/logo.png',
            quantity: 1
        });
    }

    saveCart();
    updateCartCount();
    renderCart();
    showToast(`Đã thêm "${product.name}" vào giỏ hàng`);
    console.log('Đã thêm sản phẩm vào giỏ:', product.name);
}

function showToast(message) {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function removeCartItem(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartCount();
    renderCart();
}

function updateCartQuantity(index, delta) {
    const item = cart[index];
    if (!item) return;
    item.quantity += delta;
    if (item.quantity < 1) item.quantity = 1;
    saveCart();
    updateCartCount();
    renderCart();
}

function initProductDetailsNavigation() {
    document.addEventListener('click', event => {
        const card = event.target.closest('.product-card[data-detail-url]');
        if (!card || event.target.closest('a, button, input, select, textarea, [contenteditable="true"]')) return;
        window.location.assign(card.dataset.detailUrl);
    });

    document.addEventListener('keydown', event => {
        const card = event.target.closest('.product-card[data-detail-url][role="link"]');
        if (!card || event.target !== card || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        window.location.assign(card.dataset.detailUrl);
    });
}

function initAddToCart() {
    // Render initial state
    renderCart();
    updateCartCount();

    // Event Delegation for "Add to Cart" buttons
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-to-cart');
        if (!btn) return;

        e.preventDefault();

        const productCard = btn.closest('.product-card');
        if (!productCard) return;

        const nameElement = productCard.querySelector('h3, h1');
        const priceElement = productCard.querySelector('.price-sale');
        const imageElement = productCard.querySelector('.product-image');
        const colorSelect = productCard.querySelector('.color-select');

        let productName = nameElement ? nameElement.textContent.trim() : 'Sản phẩm';
        if (colorSelect && colorSelect.value) {
            productName += ` - Màu: ${colorSelect.value}`;
        }

        const product = {
            product_id: productCard.dataset.dbId ? Number(productCard.dataset.dbId) : null,
            name: productName,
            price: priceElement ? parsePrice(priceElement.textContent) : 0,
            image: imageElement ? imageElement.src : '',
        };

        console.log('Đang thêm sản phẩm:', product);
        addItemToCart(product);

        // UI Feedback
        const originalText = btn.textContent;
        btn.textContent = 'Đã thêm!';
        btn.classList.add('added');
        btn.style.background = '#0d1b2a';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#0d1b2a';

        // Mở giỏ hàng để người dùng thấy sản phẩm đã được thêm
        toggleCartPanel(true);

        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('added');
            btn.style.background = ''; // Revert to CSS style
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 1200);

        // Pulse animation for all cart counts
        const cartCounts = document.querySelectorAll('.cart-count');
        cartCounts.forEach(count => {
            count.style.animation = 'pulse 0.6s ease';
            setTimeout(() => {
                count.style.animation = '';
            }, 600);
        });
    });

    const cartPanel = document.querySelector('.cart-panel');
    const cartOverlay = document.querySelector('.cart-overlay');
    const cartButtons = document.querySelectorAll('.cart-button');
    const cartClose = document.querySelector('.cart-close');

    function toggleCartPanel(open) {
        if (!cartPanel || !cartOverlay) return;

        if (open) {
            loadCart();
            renderCart();
            updateCartCount();
        }

        cartPanel.classList.toggle('open', open);
        cartOverlay.classList.toggle('visible', open);
    }

    cartButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleCartPanel(true);
        });
    });


    cartClose?.addEventListener('click', () => {
        toggleCartPanel(false);
    });

    cartOverlay?.addEventListener('click', () => {
        toggleCartPanel(false);
    });

    document.addEventListener('click', (event) => {
        if (!cartPanel?.classList.contains('open')) return;

        // Kiểm tra nếu click vào các nút mở giỏ hàng
        let clickedOnButton = false;
        cartButtons.forEach(btn => { if (btn.contains(event.target)) clickedOnButton = true; });

        if (cartPanel.contains(event.target) || clickedOnButton) return;

        toggleCartPanel(false);
    });

    document.querySelector('.cart-items')?.addEventListener('click', (event) => {
        const action = event.target.getAttribute('data-action');
        const cartItem = event.target.closest('.cart-item');
        const index = cartItem ? Number(cartItem.dataset.index) : -1;

        if (action === 'remove' && index >= 0) {
            removeCartItem(index);
        }

        if (action === 'decrease' && index >= 0) {
            updateCartQuantity(index, -1);
        }

        if (action === 'increase' && index >= 0) {
            updateCartQuantity(index, 1);
        }
    });

    // Xử lý nút Thanh toán
    document.querySelector('.checkout-btn')?.addEventListener('click', () => {
        const currentUser = localStorage.getItem('currentUser');
        if (!currentUser) {
            alert('Vui lòng đăng nhập tài khoản để thực hiện thanh toán!');
            const loginModal = document.getElementById('login-modal');
            if (loginModal) {
                loginModal.classList.add('active');
            }
            toggleCartPanel(false);
            return;
        }

        if (cart.length === 0) {
            alert('Giỏ hàng của bạn đang trống!');
            return;
        }

        // Khởi tạo và mở modal thanh toán
        initCheckoutModal();

        const modal = document.getElementById('checkout-modal');
        const totalPriceEl = document.getElementById('modal-total-price');

        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        if (totalPriceEl) totalPriceEl.textContent = formatPrice(total);

        if (modal) modal.classList.add('active');

        // Đóng cart panel
        toggleCartPanel(false);
    });
}

// ==================== CHECKOUT MODAL ====================
function initCheckoutModal() {
    if (document.getElementById('checkout-modal')) return;

    const modalHTML = `
    <div id="checkout-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-modal" id="close-checkout">&times;</span>
            <h2 style="margin-bottom: 20px; color: var(--navy); text-align: center;">Thông tin đặt hàng</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem;">Họ và tên</label>
                    <input type="text" id="checkout-name" placeholder="Nhập họ tên người nhận"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem;">Số điện thoại</label>
                    <input type="text" id="checkout-phone" placeholder="Nhập số điện thoại"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem;">Địa chỉ giao hàng</label>
                    <textarea id="checkout-address" placeholder="Nhập địa chỉ giao hàng chi tiết"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 80px; outline: none; resize: none; font-family: inherit; font-size: inherit;"></textarea>
                </div>
                <div style="background: var(--cream); padding: 15px; border-radius: 8px; border: 1px dashed var(--gold); margin-top: 5px;">
                    <p style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600;">Tổng tiền thanh toán:</span>
                        <strong id="modal-total-price" style="color: var(--navy); font-size: 1.3rem;">0 đ</strong>
                    </p>
                </div>
                <button id="submit-checkout"
                    style="padding: 14px; background: linear-gradient(135deg, var(--navy), var(--navy-mid)); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1.1rem; margin-top: 10px; transition: opacity 0.2s;">
                    Xác nhận đặt hàng
                </button>
            </div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const modal = document.getElementById('checkout-modal');
    const closeBtn = document.getElementById('close-checkout');
    const submitBtn = document.getElementById('submit-checkout');
    const nameInput = document.getElementById('checkout-name');
    const phoneInput = document.getElementById('checkout-phone');
    const addressInput = document.getElementById('checkout-address');

    // Pre-fill name if user is logged in
    const userJson = localStorage.getItem('currentUser');
    if (userJson) {
        const user = JSON.parse(userJson);
        if (!['admin', 'manager', 'staff'].includes(user.role)) {
            nameInput.value = user.username;
        }
    }

    closeBtn?.addEventListener('click', () => modal.classList.remove('active'));

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });

    submitBtn?.addEventListener('click', async () => {
        const name = nameInput.value.trim();
        const phone = phoneInput.value.trim();
        const address = addressInput.value.trim();

        if (!name || !phone || !address) {
            alert("Vui lòng cung cấp đầy đủ thông tin giao hàng!");
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Đang xử lý...";
        submitBtn.style.opacity = "0.7";

        try {
            const response = await fetch(appUrl('/api/orders/place_order.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cart: cart,
                    customer: { name, phone, address }
                })
            });

            const data = await response.json();
            if (data.status === 'success') {
                alert(`🎉 Đặt hàng thành công!\nMã đơn hàng của bạn là: #BLUE${data.order_id}\n\nHãy lưu lại mã này hoặc sử dụng số điện thoại để tra cứu đơn hàng nhé!`);
                cart = []; // Xóa giỏ hàng sau khi đặt thành công
                saveCart();
                updateCartCount();
                renderCart();
                modal.classList.remove('active');
            } else {
                alert("❌ Lỗi: " + data.message);
            }
        } catch (error) {
            console.error(error);
            alert("❌ Lỗi kết nối máy chủ! Vui lòng kiểm tra XAMPP.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Xác nhận đặt hàng";
            submitBtn.style.opacity = "1";
        }
    });
}

// Helper to format price from DB safely without parsing dot decimals as cents
function formatDbPrice(priceStr) {
    if (!priceStr) return '';
    const str = priceStr.toString().trim();
    if (str.includes('.') || str.includes(',')) {
        return str;
    }
    const num = Number(str);
    return isNaN(num) ? str : num.toLocaleString('vi-VN');
}

function normalizeVietnamese(str) {
    if (!str) return '';
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // remove accent marks
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'd')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();
}

// Global function to render database products in a dynamic grid
function renderDbProducts(dbProducts, container) {
    container.innerHTML = '';
    if (!dbProducts || dbProducts.length === 0) {
        container.innerHTML = '<p class="empty-cart" style="grid-column: 1/-1; text-align: center; padding: 40px; font-size: 1.1rem; color: #7a7a7a;">Không có sản phẩm nào được tìm thấy! 😢</p>';
        return;
    }

    dbProducts.forEach(product => {
        const badgeHTML = product.discount ? `<div class="product-badge">${product.discount}</div>` : '';
        const giftHTML = (product.gift && product.gift.trim() !== '') ? `<p class="product-gift" style="font-size: 0.8rem; color: var(--gold); margin: 5px 0;">🎁 ${product.gift}</p>` : '';

        const priceSaleFormatted = formatDbPrice(product.price_sale);
        const priceOriginalHTML = product.price_original && parsePrice(product.price_original) > 0
            ? `<span class="price-original" style="text-decoration: line-through; color: #999; margin-left: 10px; font-size: 0.9rem;">${formatDbPrice(product.price_original)}<sup>đ</sup></span>`
            : '';

        const stock = parseInt(product.stock, 10) || 0;
        const soldCount = parseInt(product.sold_count, 10) || 0;

        const stockHTML = stock > 0
            ? `<span class="product-stock" style="font-size: 0.85rem; color: #27ae60;">Kho: <strong>${stock}</strong></span>`
            : `<span class="product-stock" style="font-size: 0.85rem; color: #e74c3c; font-weight: 600;">Hết hàng</span>`;

        // Chỉ hiển thị lượt bán khi có đơn hàng (sold_count > 0)
        const soldHTML = soldCount > 0
            ? `<span class="sold" style="font-size: 0.85rem; color: #e67e22; font-weight: 500;">Đã bán ${soldCount}</span>`
            : '';

        const metaHTML = `
            <div class="product-meta" style="display: flex; justify-content: space-between; align-items: center; margin: 6px 0 8px;">
                ${stockHTML}
                ${soldHTML}
            </div>
        `;

        const itemHTML = `
            <div class="product-card" data-category="${product.category || ''}" data-db-id="${product.id}" data-detail-url="${appUrl('/client/product.php')}?id=${encodeURIComponent(product.id)}" role="link" tabindex="0" aria-label="Xem chi tiết sản phẩm">
                ${badgeHTML}
                <button class="wishlist-btn" title="Yêu thích">♡</button>
                <img src="${getProductImageUrl(product.image)}" alt="${product.name}" class="product-image" onerror="this.src='${appUrl('/assets/banner/logo.png')}';">
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <div class="product-price">
                        <span class="price-sale">${priceSaleFormatted}<sup>đ</sup></span>
                        ${priceOriginalHTML}
                    </div>
                    ${metaHTML}
                    ${giftHTML}
                    <button class="add-to-cart"${stock <= 0 ? ' disabled style="background:#bbb;cursor:not-allowed;"' : ''}>${stock <= 0 ? 'Tạm hết hàng' : 'Thêm vào giỏ'}</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHTML);
    });

    // Danh mục hiển thị đầy đủ sản phẩm đã tải.
    const loadMoreContainer = document.querySelector('.load-more-container');
    if (loadMoreContainer) loadMoreContainer.style.display = 'none';
}

function initSearch() {
    const searchInput = document.querySelector('.search');
    if (!searchInput) return;

    // 1. Wrap the search input in a wrapper for absolute positioning of results
    const wrapper = document.createElement('div');
    wrapper.className = 'search-wrapper';
    searchInput.parentNode.insertBefore(wrapper, searchInput);
    wrapper.appendChild(searchInput);

    // 2. Create the search results dropdown element
    const dropdown = document.createElement('div');
    dropdown.className = 'search-results-dropdown';
    wrapper.appendChild(dropdown);

    let allProducts = [];
    let isFetched = false;
    let fetchPromise = null;
    let loadError = false;

    // Preload products when user focuses the search input to ensure instant typing response
    async function preloadProducts() {
        if (isFetched) return allProducts;
        if (fetchPromise) return fetchPromise;

        fetchPromise = (async () => {
            try {
                const response = await fetch(appUrl('/api/catalog/get_products.php'));
                if (!response.ok) throw new Error('Không thể tải danh sách sản phẩm.');
                const products = await response.json();
                if (!Array.isArray(products)) throw new Error('Dữ liệu sản phẩm không hợp lệ.');
                allProducts = products;
                isFetched = true;
                loadError = false;
                console.log('🔍 Đã tải trước danh sách sản phẩm phục vụ tìm kiếm:', allProducts.length);
            } catch (error) {
                loadError = true;
                console.error('Lỗi tải danh sách sản phẩm cho tìm kiếm:', error);
            } finally {
                fetchPromise = null;
            }

            return allProducts;
        })();

        return fetchPromise;
    }

    searchInput.addEventListener('focus', async () => {
        await preloadProducts();
        if (searchInput.value.trim()) updateSearchResults();
    });

    searchInput.addEventListener('input', updateSearchResults);

    async function updateSearchResults() {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        dropdown.textContent = 'Đang tìm sản phẩm...';
        dropdown.classList.add('active');
        const products = await preloadProducts();
        if (searchInput.value.trim().toLowerCase() !== query) return;
        if (loadError) {
            dropdown.textContent = 'Không tải được sản phẩm. Vui lòng thử lại.';
            dropdown.classList.add('active');
            return;
        }

        const filtered = products.filter(product =>
            product.name && product.name.toLowerCase().includes(query)
        );

        renderSearchResults(filtered, dropdown);
    }

    // Close search dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Handle pressing Enter to search on page
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const query = searchInput.value.trim();
            dropdown.classList.remove('active');

            if (query) {
                handleScreenSearch(query);
            }
        }
    });

    async function handleScreenSearch(query) {
        const container = document.getElementById('category-products-container');
        if (container) {
            // Make sure we have loaded all database products for this category/page or general
            let searchProducts = allProducts;
            if (searchProducts.length === 0) {
                try {
                    const response = await fetch(appUrl('/api/catalog/get_products.php'));
                    if (response.ok) {
                        searchProducts = await response.json();
                    }
                } catch (err) {
                    console.error(err);
                }
            }

            // Filter products
            const filtered = searchProducts.filter(product =>
                product.name && product.name.toLowerCase().includes(query.toLowerCase())
            );

            // Render matching products directly on page screen grid!
            renderDbProducts(filtered, container);
            applyUserRole(); // Ensure edit privileges are shown if Admin is logged in

            // Update screen title dynamically
            const pageTitle = document.querySelector('.page-title-left') || document.querySelector('.page-title');
            if (pageTitle) {
                pageTitle.textContent = `KẾT QUẢ TÌM KIẾM: "${query.toUpperCase()}"`;
            }
        } else {
            // If on homepage or other page without a product grid, redirect to sonmoi.html with query parameter
            window.location.href = `${appUrl('/index.php')}?search=${encodeURIComponent(query)}`;
        }
    }

    function renderSearchResults(products, container) {
        container.innerHTML = '';
        if (products.length === 0) {
            container.innerHTML = '<div class="search-result-empty">Không tìm thấy sản phẩm nào khớp! 😢</div>';
            container.classList.add('active');
            return;
        }

        products.forEach(product => {
            const priceSaleFormatted = formatDbPrice(product.price_sale) + ' đ';
            const imagePath = typeof product.image === 'string' ? product.image : '';
            const imageUrl = imagePath.startsWith('/') || /^https?:\/\//i.test(imagePath)
                ? imagePath
                : appUrl(imagePath.replace(/^(?:\.\.\/)+/, ''));

            const stock = parseInt(product.stock, 10) || 0;
            const stockInfo = stock > 0 ? `<small style="color: #27ae60; font-size: 0.8rem;">Kho: ${stock}</small>` : `<small style="color: #e74c3c; font-size: 0.8rem;">Hết hàng</small>`;

            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.innerHTML = `
                <img src="${imageUrl}" alt="${product.name}" class="search-result-image" onerror="this.src='${appUrl('/assets/banner/logo.png')}';">
                <div class="search-result-info">
                    <p class="search-result-name">${product.name}</p>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span class="search-result-price">${priceSaleFormatted}</span>
                        ${stockInfo}
                    </div>
                </div>
                <button class="search-result-btn"${stock <= 0 ? ' disabled style="background:#bbb;cursor:not-allowed;"' : ''}>${stock <= 0 ? 'Hết hàng' : 'Thêm vào giỏ'}</button>
            `;

            // Handle add to cart click
            const btn = item.querySelector('.search-result-btn');
            btn.addEventListener('click', (e) => {
                e.stopPropagation(); // Avoid triggering parent item click

                const prod = {
                    product_id: Number(product.id),
                    name: product.name,
                    price: parsePrice(product.price_sale), // Fixed pricing bug using parsePrice
                    image: product.image
                };

                addItemToCart(prod);

                // Temporary feedback state
                btn.textContent = 'Đã thêm!';
                btn.style.background = '#4CAF50';
                setTimeout(() => {
                    btn.textContent = 'Thêm vào giỏ';
                    btn.style.background = '';
                }, 1200);
            });

            // Make whole row clickable to add to cart
            item.addEventListener('click', () => {
                btn.click();
            });

            container.appendChild(item);
        });

        container.classList.add('active');
    }
}


// ==================== TAB FUNCTIONALITY ====================
function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Xóa class active từ tất cả buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            // Thêm class active vào button được click
            btn.classList.add('active');

            const tabName = btn.getAttribute('data-tab');
            console.log('Switched to tab:', tabName);
            // Thêm logic để hiển thị/ẩn content theo tab
        });
    });
}

// ==================== AUTHENTICATION & RBAC ====================
const REQUIRED_PASSWORD_LENGTH = 8;

function initAuth() {
    const loginTriggers = document.querySelectorAll('.login-trigger');
    const loginModal = document.getElementById('login-modal');
    const closeLogin = document.getElementById('close-login');
    const submitLogin = document.getElementById('submit-login');
    const usernameInput = document.getElementById('login-username');
    const passwordInput = document.getElementById('login-password');
    const authContainer = document.getElementById('auth-container');

    const registerModal = document.getElementById('register-modal');
    const closeRegister = document.getElementById('close-register');

    // Mở modal đăng nhập
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('login-trigger')) {
            if (registerModal) registerModal.classList.remove('active');
            if (loginModal) loginModal.classList.add('active');
            const lm = document.getElementById('login-message');
            if (lm) lm.textContent = '';
        }
    });

    // Mở modal đăng ký
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('register-trigger')) {
            if (loginModal) loginModal.classList.remove('active');
            if (registerModal) registerModal.classList.add('active');
            const rm = document.getElementById('register-message');
            if (rm) rm.textContent = '';
        }
    });

    // Đóng modal
    if (closeLogin) {
        closeLogin.addEventListener('click', () => {
            loginModal.classList.remove('active');
        });
    }
    if (closeRegister) {
        closeRegister.addEventListener('click', () => {
            registerModal.classList.remove('active');
        });
    }

    // Xử lý đăng nhập qua PHP
    if (submitLogin) {
        submitLogin.addEventListener('click', async () => {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            const loginMsg = document.getElementById('login-message');

            const showLoginMsg = (text, color) => {
                if (loginMsg) {
                    loginMsg.textContent = text;
                    loginMsg.style.color = color || '#c0392b';
                }
            };

            if (!username || !password) {
                showLoginMsg('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');
                return;
            }

            showLoginMsg('Đang đăng nhập...', '#666');
            submitLogin.disabled = true;

            try {
                const response = await fetch(appUrl('/api/auth/login.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Tác dụng: Phân tách luồng sau đăng nhập - Khách hàng lưu phiên mua sắm, Quản trị viên chuyển tiếp về bảng quản trị
                    if (data.role === 'customer') {
                        const user = { username: data.username, role: data.role };
                        localStorage.setItem('currentUser', JSON.stringify(user));
                        loginModal.classList.remove('active');
                        applyUserRole();
                        window.location.reload();
                    } else {
                        localStorage.removeItem('currentUser');
                        loginModal.classList.remove('active');
                        window.location.href = appUrl('/admin/index.php');
                    }
                } else {
                    showLoginMsg(data.message || 'Tên đăng nhập hoặc mật khẩu không đúng.');
                }
            } catch (error) {
                console.error(error);
                showLoginMsg('Lỗi kết nối máy chủ. Vui lòng kiểm tra XAMPP.');
            } finally {
                submitLogin.disabled = false;
            }
        });
    }

    // Xử lý Đăng ký qua PHP
    const submitRegister = document.getElementById('submit-register');
    const registerUsernameInput = document.getElementById('register-username');
    const registerPhoneInput = document.getElementById('register-phone');
    const registerPasswordInput = document.getElementById('register-password');
    const registerConfirmPasswordInput = document.getElementById('register-confirm-password');

    if (submitRegister) {
        submitRegister.addEventListener('click', async () => {
            const username        = registerUsernameInput.value.trim();
            const phone           = registerPhoneInput ? registerPhoneInput.value.trim() : '';
            const password        = registerPasswordInput.value;
            const confirmPassword = registerConfirmPasswordInput.value;
            const regMsg          = document.getElementById('register-message');

            const showRegMsg = (text, color) => {
                if (regMsg) {
                    regMsg.textContent = text;
                    regMsg.style.color = color || '#c0392b';
                }
            };

            if (!username || !phone || !password || !confirmPassword) {
                showRegMsg('Vui lòng nhập đầy đủ thông tin để đăng ký!');
                return;
            }

            if (!/^0[0-9]{9}$/.test(phone.replace(/[\s().-]/g, ''))) {
                showRegMsg('Số điện thoại không hợp lệ (phải đúng 10 số, ví dụ: 0901234567)');
                return;
            }
            const isValidPassword = (pwd) => {
                return pwd.length === 8 &&
                    /[a-z]/.test(pwd) &&
                    /[A-Z]/.test(pwd) &&
                    /[0-9]/.test(pwd) &&
                    /[^a-zA-Z0-9]/.test(pwd);
            };

            if (!isValidPassword(password)) {
                showRegMsg('Mật khẩu phải đúng 8 ký tự, gồm chữ thường, chữ in hoa, số và ký tự đặc biệt (ví dụ: Abc@1234).');
                return;
            }
            if (password !== confirmPassword) {
                showRegMsg('Mật khẩu nhập lại không khớp!');
                return;
            }

            showRegMsg('Đang xử lý...', '#666');
            submitRegister.disabled = true;

            try {
                const response = await fetch(appUrl('/api/auth/register.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, phone, password })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    alert(data.message);
                    registerModal?.classList.remove('active');
                    loginModal?.classList.add('active');
                    if (usernameInput) usernameInput.value = username;
                    if (registerPhoneInput) registerPhoneInput.value = '';
                    registerUsernameInput.value = '';
                    registerPasswordInput.value = '';
                    registerConfirmPasswordInput.value = '';
                } else {
                    showRegMsg(data.message || 'Không thể đăng ký lúc này.');
                }
            } catch (error) {
                console.error(error);
                showRegMsg('Lỗi kết nối máy chủ. Vui lòng kiểm tra XAMPP.');
            } finally {
                submitRegister.disabled = false;
            }
        });
    }

    // Đăng xuất qua PHP
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('logout-btn')) {
            try {
                const response = await fetch(appUrl('/api/auth/logout.php'));
                const data = await response.json();
                if (data.status === 'success') {
                    localStorage.removeItem('currentUser');
                    applyUserRole();
                    alert('Đã đăng xuất thành công!');
                    window.location.reload(); // Load lại trang để cập nhật trạng thái sạch
                }
            } catch (error) {
                console.error(error);
                alert('Lỗi khi đăng xuất!');
            }
        }
    });

    // Kiểm tra session từ server khi khởi tạo
    checkSession();
}

// Tác dụng: Đồng bộ trạng thái đăng nhập của khách hàng từ máy chủ về trình duyệt, loại bỏ dữ liệu nếu phiên đã hết hạn
async function checkSession() {
    try {
        const response = await fetch(appUrl('/api/auth/check_session.php'));
        const data = await response.json();
        if (data.status === 'success' && data.role === 'customer') {
            const user = { username: data.username, role: data.role };
            localStorage.setItem('currentUser', JSON.stringify(user));
        } else {
            localStorage.removeItem('currentUser');
        }
        applyUserRole();
    } catch (error) {
        console.error('Session check failed:', error);
    }
}

// Tác dụng: Hiển thị thanh trạng thái người dùng tại giao diện khách hàng (chào tên khách hàng nếu đã đăng nhập hoặc hiển thị nút Đăng ký / Đăng nhập)
function applyUserRole() {
    const userJson = localStorage.getItem('currentUser');
    const authContainer = document.getElementById('auth-container');
    const adminFooterLink = document.getElementById('admin-footer-link');

    if (adminFooterLink) {
        adminFooterLink.style.display = 'none';
    }

    if (userJson) {
        try {
            const user = JSON.parse(userJson);
            if (user && user.role === 'customer') {
                if (authContainer) {
                    authContainer.innerHTML = `
                        <span style="font-weight: bold; color: var(--navy);">Xin chào, ${user.username}</span> | 
                        <span class="auth-btn logout-btn" style="cursor: pointer;">Đăng xuất</span>
                    `;
                }
                return;
            }
        } catch (e) {
            localStorage.removeItem('currentUser');
        }
    }

    // Hiển thị nút đăng ký / đăng nhập mặc định cho khách vãng lai
    if (authContainer) {
        authContainer.innerHTML = `
            <span class="auth-btn register-trigger" style="cursor: pointer;">Đăng ký</span> / 
            <span class="auth-btn login-trigger" style="cursor: pointer;">Đăng nhập</span>
        `;
    }
}

// ==================== MENU TOGGLE ====================
function initMenuToggle() {
    const menuToggle = document.querySelector('.menu-toggle');
    const menuContainer = document.querySelector('.menu-container');

    if (!menuToggle || !menuContainer) return;

    menuToggle.addEventListener('click', () => {
        menuContainer.classList.toggle('active');
    });
}

// ==================== HOVER EFFECTS ====================
function initHoverEffects() {
    const menuItems = document.querySelectorAll('.menu-item');

    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.borderBottom = '3px solid var(--gold)';
            this.style.paddingBottom = '3px';
        });

        item.addEventListener('mouseleave', function () {
            this.style.borderBottom = 'none';
            this.style.paddingBottom = '6px';
        });
    });
}

// ==================== ANIMATIONS ====================
function addPulseAnimation() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease;
        }
    `;
    document.head.appendChild(style);
}

// ==================== LAZY LOADING IMAGES ====================
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// ==================== SMOOTH SCROLL ====================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// ==================== STICKY HEADER ====================
function initStickyHeader() {
    const header = document.querySelector('.header');
    const menu = document.querySelector('.menu');

    if (header && menu) {
        const wrapper = document.createElement('div');
        wrapper.className = 'sticky-header-wrapper';
        header.parentNode.insertBefore(wrapper, header);
        wrapper.appendChild(header);
        wrapper.appendChild(menu);
    }
}

// ==================== CATEGORY FILTERS & SORTING ====================
function initCategoryFilters() {
    const productGrid = document.querySelector('.category-grid');
    if (!productGrid) return;

    const filterBtn = document.querySelector('.apply-filter-btn');
    const sortSelect = document.querySelector('.sorting-options select');

    // Lấy input an toàn hơn
    const priceInputs = document.querySelectorAll('.price-filter input');
    const priceFrom = priceInputs.length > 0 ? priceInputs[0] : null;
    const priceTo = priceInputs.length > 1 ? priceInputs[1] : null;

    // Lọc checkboxes động dựa theo tiêu đề nhóm lọc để tránh lỗi thứ tự thẻ (nth-of-type)
    let brandCheckboxes = [];
    let typeCheckboxes = [];

    document.querySelectorAll('.filter-group').forEach(group => {
        const groupTitleEl = group.querySelector('h3');
        if (!groupTitleEl) return;
        const titleText = groupTitleEl.textContent.trim().toUpperCase();

        if (titleText.includes('THƯƠNG HIỆU')) {
            brandCheckboxes = Array.from(group.querySelectorAll('input[type="checkbox"]'));
        } else if (titleText.includes('LOẠI SẢN PHẨM') || titleText.includes('LOẠI SON') || titleText.includes('LOẠI DA')) {
            typeCheckboxes = Array.from(group.querySelectorAll('input[type="checkbox"]'));
        }
    });

    function parsePriceValue(priceStr) {
        if (!priceStr) return 0;
        return Number(priceStr.replace(/[^\d]/g, '')) || 0;
    }

    function parseSoldValue(text) {
        if (!text) return 0;
        let num = parseFloat(text.replace(/[^\d.]/g, '')) || 0;
        if (text.toLowerCase().includes('k')) num *= 1000;
        return num;
    }

    function parseVietnamesePriceInput(valStr) {
        if (!valStr) return 0;
        let cleaned = valStr.replace(/[\.\,\s]/g, '');
        let num = Number(cleaned);
        if (isNaN(num)) return 0;
        // Nếu người dùng nhập 99 hoặc 500 thì tự động nhân 1000 thành 99k, 500k
        if (num > 0 && num < 10000) {
            num *= 1000;
        }
        return num;
    }

    function applyFilters() {
        const activeBrands = brandCheckboxes
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.closest('label').textContent.trim().toLowerCase(); });

        const activeTypeCheckboxes = typeCheckboxes.filter(function (cb) { return cb.checked; });

        let minPrice = 0;
        if (priceFrom && priceFrom.value.trim() !== "") {
            minPrice = parseVietnamesePriceInput(priceFrom.value);
        }

        let maxPrice = Infinity;
        if (priceTo && priceTo.value.trim() !== "") {
            maxPrice = parseVietnamesePriceInput(priceTo.value);
        }

        const hasActiveFilters = activeBrands.length > 0 ||
            activeTypeCheckboxes.length > 0 ||
            (priceFrom && priceFrom.value.trim() !== "") ||
            (priceTo && priceTo.value.trim() !== "");

        const loadMoreContainer = document.querySelector('.load-more-container');
        const loadMoreBtn = document.getElementById('load-more-btn');

        if (loadMoreContainer) {
            loadMoreContainer.style.display = hasActiveFilters ? 'none' : 'block';
            if (loadMoreBtn && !hasActiveFilters) {
                loadMoreBtn.textContent = 'Xem thêm';
            }
        }

        const currentProducts = Array.from(productGrid.querySelectorAll('.product-card'));
        currentProducts.forEach(function (card) {
            const brandEl = card.querySelector('.product-brand');
            const titleEl = card.querySelector('h3');
            const priceEl = card.querySelector('.price-sale');

            const brand = brandEl ? brandEl.textContent.trim().toLowerCase() : '';
            const title = titleEl ? titleEl.textContent.trim().toLowerCase() : '';
            const price = priceEl ? parsePriceValue(priceEl.textContent) : 0;
            const cardCategory = card.getAttribute('data-category') || '';

            if (hasActiveFilters) {
                let showBrand = activeBrands.length === 0 || activeBrands.includes(brand);

                let showType = activeTypeCheckboxes.length === 0;
                if (!showType) {
                    showType = activeTypeCheckboxes.some(function (cb) {
                        const typeText = cb.closest('label').textContent.trim().toLowerCase();
                        const typeValue = cb.value;

                        // 1. Nếu checkbox có thuộc tính value cụ thể (như sua-rua-mat, phan-nuoc,...)
                        if (typeValue && typeValue !== 'on') {
                            if (cardCategory === typeValue) return true;
                        }

                        // 2. Dự phòng so khớp theo tên sản phẩm
                        if (typeText.includes('lì') && (title.includes('lì') || title.includes('kem min') || title.includes('kem mịn'))) return true;
                        if (typeText.includes('tint') && (title.includes('tint') || title.includes('bóng'))) return true;

                        return false;
                    });
                }

                let showPrice = price >= minPrice && price <= maxPrice;

                if (showBrand && showType && showPrice) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            } else {
                // Không có bộ lọc nào kích hoạt: phục hồi trạng thái phân trang mặc định (hiện 6, ẩn extra)
                if (card.classList.contains('extra-product')) {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                }
            }
        });

        applySorting();
    }

    function applySorting() {
        if (!sortSelect) return;
        const sortType = sortSelect.value;
        const visibleProducts = Array.from(productGrid.querySelectorAll('.product-card'));

        visibleProducts.sort(function (a, b) {
            const priceElA = a.querySelector('.price-sale');
            const priceElB = b.querySelector('.price-sale');
            const priceA = priceElA ? parsePriceValue(priceElA.textContent) : 0;
            const priceB = priceElB ? parsePriceValue(priceElB.textContent) : 0;

            const soldElA = a.querySelector('.sold');
            const soldElB = b.querySelector('.sold');
            const soldA = soldElA ? parseSoldValue(soldElA.textContent) : 0;
            const soldB = soldElB ? parseSoldValue(soldElB.textContent) : 0;

            if (sortType === 'Giá từ thấp đến cao') return priceA - priceB;
            if (sortType === 'Giá từ cao đến thấp') return priceB - priceA;
            if (sortType === 'Bán chạy nhất') return soldB - soldA;

            return 0;
        });

        visibleProducts.forEach(function (card) {
            productGrid.appendChild(card);
        });
    }

    // Gắn sự kiện Lọc/Sắp xếp
    if (filterBtn) {
        filterBtn.addEventListener('click', applyFilters);
    }
    brandCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', applyFilters);
    });
    typeCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', applyFilters);
    });
    if (sortSelect) {
        sortSelect.addEventListener('change', applySorting);
    }

    // Quản lý nút "Xem thêm" toàn cục (Global Load More)
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        // Thay thế hoặc gắn trực tiếp sự kiện (đã an toàn nhờ DOMContentLoaded chạy 1 lần duy nhất)
        loadMoreBtn.addEventListener('click', function () {
            const extraProducts = document.querySelectorAll('.extra-product');
            if (extraProducts.length === 0) return;

            const isHidden = extraProducts[0].style.display === 'none';
            extraProducts.forEach(prod => {
                prod.style.display = isHidden ? 'block' : 'none';
            });
            loadMoreBtn.textContent = isHidden ? 'Thu gọn' : 'Xem thêm';
        });
    }
}

// ==================== DYNAMIC CATEGORY PAGES ====================
async function initDynamicCategoryPages() {
    const container = document.getElementById('category-products-container');
    if (!container) return;

    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    if (searchQuery) {
        const searchSection = document.getElementById('search-results-section');
        if (searchSection) searchSection.hidden = false;
        const searchInput = document.querySelector('.search');
        if (searchInput) searchInput.value = searchQuery;

        try {
            const response = await fetch(appUrl('/api/catalog/get_products.php'));
            if (!response.ok) throw new Error('Không thể tải danh sách sản phẩm để tìm kiếm.');
            const products = await response.json();
            const normalizedQuery = searchQuery.trim().toLowerCase();
            const filteredProducts = products.filter(product =>
                product.name && product.name.toLowerCase().includes(normalizedQuery)
            );

            renderDbProducts(filteredProducts, container);
            const pageTitle = document.querySelector('.page-title-left');
            if (pageTitle) pageTitle.textContent = `KẾT QUẢ TÌM KIẾM: "${searchQuery.toUpperCase()}"`;
            applyUserRole();
        } catch (error) {
            console.error('Lỗi khi tìm kiếm sản phẩm:', error);
            container.innerHTML = '<p class="empty-cart" style="grid-column: 1/-1; text-align: center;">Không tìm kiếm được sản phẩm. Vui lòng kiểm tra kết nối cơ sở dữ liệu.</p>';
        }
        return;
    }

    const dbCategory = container.getAttribute('data-db-category');
    if (!dbCategory) return;

    console.log(`Loading dynamic products for category: ${dbCategory}...`);
    try {
        const response = await fetch(`${appUrl('/api/catalog/get_products.php')}?category=${encodeURIComponent(dbCategory)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const dbProducts = await response.json();

        if (dbProducts && dbProducts.length > 0) {
            console.log(`Loaded ${dbProducts.length} products from database. Rendering dynamically...`);

            renderDbProducts(dbProducts, container);

            // Hiển thị hay ẩn nút "Xem thêm" được xử lý tự động trong renderDbProducts tùy theo số sản phẩm thực tế từ DB

            // Sau khi render xong các thẻ động, cập nhật lại hiển thị nút "Chỉnh sửa sản phẩm" dựa trên quyền Admin
            applyUserRole();
        } else {
            container.innerHTML = '<p class="empty-category-message">Danh mục hiện chưa có sản phẩm.</p>';
        }
    } catch (error) {
        console.error("Lỗi khi tải sản phẩm động:", error);
        container.innerHTML = '<p class="empty-cart" style="grid-column: 1/-1; text-align: center;">Không tải được sản phẩm. Vui lòng kiểm tra kết nối cơ sở dữ liệu.</p>';
    }
}

// ==================== INITIALIZE ALL ====================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('🚀 Website đã sẵn sàng!');

    addPulseAnimation();
    initStickyHeader();
    initSlideshow();
    initComboSlider();
    initScrollToTop();
    initProductDetailsNavigation();
    initAddToCart();
    initSearch();
    initTabs();
    initAuth();
    initMenuToggle();
    initHoverEffects();
    initLazyLoading();
    initSmoothScroll();

    // Tải động sản phẩm danh mục trước khi gắn bộ lọc
    await initDynamicCategoryPages();
    initCategoryFilters();

    console.log('✅ Tất cả các tính năng đã được khởi tạo!');
});

// ==================== VIEWPORT CHECK ====================
window.addEventListener('load', () => {
    console.log('📱 Window width:', window.innerWidth);
    console.log('📱 Window height:', window.innerHeight);
});

// ==================== DEBUG MODE ====================
const isDebugMode = false; // Thay đổi thành true để bật debug mode

if (isDebugMode) {
    console.log('🐛 DEBUG MODE ON');
    window.addEventListener('scroll', () => {
        console.log('Scroll Y:', window.scrollY);
    });
}

// ==================== TAB SYNC ====================
window.addEventListener('storage', (e) => {
    if (e.key === 'cart') {
        loadCart();
        updateCartCount();
        renderCart();
    }
});
