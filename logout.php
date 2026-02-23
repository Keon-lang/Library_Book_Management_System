<?php
session_start();
session_destroy();
?>
<script type="text/javascript">
    alert('Success Logout !');
    location.href ="login.php";
</script>
