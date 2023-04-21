<?php if (!\Imon\Debug::$boxcss) {?>
<style>
.imdebug-box {
    background-color: #f9f9f9;
    border-left: 3px solid #5ea3aa;
    padding: 5px 10px;
    color: #666c70;
}

.imdebug-box+.imdebug-box {
    border-top: 1px solid #eee;
}

.imdebug-hide {
    display: none;
}

.imdebug-box ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.imdebug-ref>ul {
    display: none;
    margin-left: 22px;
}

.imdebug-note {
    color: #ff5e00;
    cursor: pointer;
}

.imdebug-key {
    color: #21c773;
}

.imdebug-mod {
    color: #c51777;
}

.imdebug-method {
    color: #1590cb;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.imdebug-ref').forEach(function(el) {
        el.querySelector('.imdebug-note').addEventListener('click', function(e) {
            var ul = el.querySelector('ul')
            ul.style.display = ul.style.display == 'block' ? 'none' : 'block'
            e.stopPropagation()
        }, false)
    })
})
</script>
<?php \Imon\Debug::$boxcss = true;?>
<?php }?>

<?php if (empty($data)) {return;}?>

<div class="imdebug-box <?php echo $class; ?>">
    <?php \Imon\Debug::varHTML($var);?>
</div>