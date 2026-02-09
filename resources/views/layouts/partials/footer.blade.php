<footer class="footer px-4">
    <div>
        {{--        Powered by&nbsp;<a href="https://woopi.com.ar/" target="_blank" rel="noopener noreferrer">Woopi</a>--}}
    </div>
    <div class="ms-auto">
        <a href="{{ route('chat.index') }}">YPF Chat Station</a>
        &copy; <span id="footer-year"></span>
    </div>
</footer>
<script>
    document.getElementById('footer-year').textContent = new Date().getFullYear();
</script>
