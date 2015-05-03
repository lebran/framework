<?=$files?>

<div id="debug-toolbar">
    <div id="debug-toolbar-header">   
        <ul>
            <?php foreach ($tab_keys as $link => $name): ?>
            <li><a href="#<?=$link?>" ><?=$name?></a></li>
            <?php endforeach;?>
            <li id="close"><a href="#close" >Close</a></li>
        </ul>  
    </div>
    <div id="debug-toolbar-container">		
        <?php foreach ($tabs as $link => $tab): ?>
        <div id="<?=$link?>" class="debug-tab">
            <?=$tab?>
        </div>
        <?php endforeach;?>
    </div>	
</div>