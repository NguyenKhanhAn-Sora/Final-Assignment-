console.log('Debug.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    const viewButtons = document.querySelectorAll('.view-product');
    console.log('View buttons found:', viewButtons.length);
    
    const editButtons = document.querySelectorAll('.edit-product');
    console.log('Edit buttons found:', editButtons.length);
    
    const deleteButtons = document.querySelectorAll('.delete-product');
    console.log('Delete buttons found:', deleteButtons.length);
});
