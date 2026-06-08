    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Delta State Polytechnic, Otefe-Oghara, Delta State, Nigeria.</p>
       
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/quiz.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/editor.js"></script>
<?php foreach ($extraJs as $js): ?>
    <script src="<?php echo htmlspecialchars($js); ?>"></script>
<?php endforeach; ?>
</body>
</html>
