<footer class="footer px-4">
    <div>
        <a href="{{ route('chat.index') }}">YPF Chat Station</a>
        &copy; <span id="footer-year"></span>
    </div>
    <div class="ms-auto">
        Powered by&nbsp;<a href="https://coreui.io/" target="_blank" rel="noopener noreferrer">CoreUI</a>
    </div>
</footer>
<script>
    document.getElementById('footer-year').textContent = new Date().getFullYear();
</script>
