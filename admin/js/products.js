// Function xóa sản phẩm
function deleteProduct(productId) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        // Chuyển hướng đến trang xóa sản phẩm với ID
        window.location.href = `/Assignment/admin/includes/delete_product.php?id=${productId}`;
    }
}

// Xử lý preview ảnh khi chọn
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('productImages');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }
});

// Xử lý preview ảnh
function handleImagePreview(event) {
    const container = document.getElementById('imagePreviewContainer');
    const dropText = document.getElementById('dropText');
    
    // Clear existing previews but keep the dropText
    while (container.firstChild && container.firstChild !== dropText) {
        container.removeChild(container.firstChild);
    }

    if (this.files && this.files.length > 0) {
        dropText.style.display = 'none';
        
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                const previewDiv = document.createElement('div');
                previewDiv.className = 'position-relative d-inline-block m-2';
                
                reader.onload = function(e) {
                    previewDiv.innerHTML = `
                        <div class="image-preview" style="width: 150px; height: 150px; overflow: hidden; border-radius: 8px; border: 2px solid #ddd;">
                            <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                    style="top: 5px; right: 5px; border-radius: 50%; width: 25px; height: 25px; padding: 0;"
                                    onclick="removeImage(this, ${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                };
                
                reader.readAsDataURL(file);
                container.insertBefore(previewDiv, dropText);
            }
        });
    } else {
        dropText.style.display = 'block';
    }
}

// Function xóa ảnh preview
function removeImage(button, index) {
    const input = document.getElementById('productImages');
    const container = document.getElementById('imagePreviewContainer');
    const dropText = document.getElementById('dropText');
    
    button.closest('.position-relative').remove();
    
    const dt = new DataTransfer();
    const { files } = input;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    input.files = dt.files;
    
    if (container.children.length === 1) {
        dropText.style.display = 'block';
    }
}
