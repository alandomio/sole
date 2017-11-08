<?php
# V.0.1.10
ob_start();
?>
<div id="paginazione"><div id="content_paginazione"><?
for($i=0;$i<7;$i++){?>
<?=$player_curs->player[$i]; }?>
</div><div class="clear"></div>
</div><? 
$player=ob_get_clean();
?>