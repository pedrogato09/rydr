<?php require "includes/header.php" ?>
<main>
    <form action="/login-handler" class="account-form" method="post">
        <h2>Log in</h2>
        <?php if (isset($_SESSION['success'])) { ?>
            <div class="succes-message"><?= $_SESSION['success'] ?></div>
        <?php } ?>
        <label for="name">Your name</label>
        <input type="text" name="name" id="name" placeholder="John Doe" value="<?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '' ?>" required>
        <label for="email">Your email</label>
        <input type="email" name="email" id="email" placeholder="johndoe@gmail.com" value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>" required autofocus>
        <label for="password">Your password</label>
        <input type="password" name="password" id="password" placeholder="Your password" required>
        <input type="submit" value="Log in" class="button-primary">
    </form>
</main>

<?php require "includes/footer.php" ?>
