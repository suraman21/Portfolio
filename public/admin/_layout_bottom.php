    </main>
  </div>
  <script>
    (function () {
      var btn = document.getElementById('adminSidebarToggle');
      var shell = document.getElementById('adminShell');
      if (btn && shell) {
        btn.addEventListener('click', function () {
          shell.classList.toggle('sidebar-open');
        });
      }
    })();
  </script>
</body>
</html>
