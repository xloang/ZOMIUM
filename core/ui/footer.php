<style>
.hidden { display: none; }

.app-footer {
    background: #121212;
}

.app-footer-inner {
    background: transparent !important; /* box color yes */
    border-radius: 0px !important;
    padding: 20px;
}

.app-footer-inner {
    background: transparent !important;
    border-radius: 0px !important;
    padding: 20px;
    border: 1px solid #1f1f1f;
}

.d-flex a {
    color: #11b0ff;
    text-decoration: none;
}

.d-flex a:hover {
    color: #0a8cd1; 
    text-decoration: underline;
}


</style>

<footer class="app-footer mt-auto py-4">


    <div class="container">
        <div class="app-footer-inner d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

            
            <div>
                <div class="fw-bold">Zomium</div>
                <div class="small text-muted">made by xloang (西洛昂)</div>
                <div class="small mt-1">
                    <b>Lucky number:</b> <?= rand(1, 1000) ?> <!-- my lucky number -->
                </div>
            </div>

            
            <div class="d-flex flex-wrap gap-3 small">
                <a href="/info/about.php">About Us</a> |
                <a href="/info/terms.php">Terms</a> |
                <a href="/info/privacy.php">Privacy Policy</a> |
                <a href="/download">Download</a> |
                <a href="https://discord.gg/5RsJrCCAzx">Discord</a>
            </div>

            
            <div class="small text-lg-end">

                &copy; <?= date('Y') ?> Zomium
                <div class="dropdown mt-2">
                    <button class="btn btn-theme btn-sm" data-bs-toggle="dropdown">
                        🌐
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">English (US)</a>
                        <a class="dropdown-item" href="#">English (Canada)</a>
                        <a class="dropdown-item" href="#">中文(繁體)</a>
                        <a class="dropdown-item" href="#">Português (Brasil)</a>
                        <a class="dropdown-item" href="#">Español</a>
                        <a class="dropdown-item" href="#">Polski</a>
                        <a class="dropdown-item" href="#">Tagalog</a>
                        <a class="dropdown-item" href="#">Türkçe</a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</footer>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
if(!document.getElementById('ZMRrwOVaKiIQ')) {
    function toggleElement() {
        var parentDiv = document.querySelector(".nav-scroller");
        var alert = document.getElementById("dynamicElement");

        if (!alert && parentDiv) {
            parentDiv.insertAdjacentHTML("afterend",
                '<div id="dynamicElement" class="alert alert-danger text-center" style="display:block;color:rgba(255,255,255,0.9);font-size:1.25rem;">Please disable AdBlock to support us.</div>'
            );
        }
    }

    setInterval(toggleElement, 100);
}
</script>

