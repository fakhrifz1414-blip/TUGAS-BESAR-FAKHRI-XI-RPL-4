<?php if (isset($_SESSION['user_id'])): ?>
    </main>
    <footer class="app-foot">SIPN &copy; <?= date('Y') ?> &middot; SMK Sangkuriang 1 Cimahi</footer>
  </div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
  // buka/tutup sidebar di layar kecil
  document.querySelectorAll('[data-sb-toggle]').forEach(function (el) {
    el.addEventListener('click', function () { document.body.classList.toggle('sb-open'); });
  });
</script>
  </body>
</html>
