<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
.top-nav {
    width: 100%;
    background: rgba(255,255,255,0.9);
    padding: 10px 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 999;
}

.nav-left button {
    padding: 8px 16px;
    margin-right: 8px;
    border: none;
    border-radius: 20px;
    background: linear-gradient(90deg,#b9fbc0,#ffc6d9);
    cursor: pointer;
    font-weight: 600;
}

.nav-title {
    font-weight: bold;
    color: #2d6a4f;
    position: relative;
    left:-20px;
    }
</style>

<div class="top-nav">

    <div class="nav-left">
        <button onclick="goBack()">⬅ Back</button>
        <button onclick="goHome()">🏠 Home</button>
    </div>

    <div class="nav-title">
        EKM Application
    </div>

</div>

<script>
function goBack() {
    window.history.back();
}

function goHome() {
    window.location.href = "dashboard.php";
}
</script>
