document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quick-reply-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelector('textarea[name="reply_message"]').value += this.dataset.message;
        });
    });
});