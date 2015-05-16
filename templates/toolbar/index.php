<?=$files?>

<div id="debug-toolbar">
    <div id="debug-toolbar-header">   
        <ul>
            <li class="left-tab"><img class="logo" src="templates/<?=$template?>/img/logo1.png"></li>
            <li class="right-tab" id="close"><a class="close" href="#close" ><img class="tab-logo" src="templates/<?=$template?>/img/cancel.png"></a></li>
            <?php foreach ($tab_keys as $link => $name): ?>
            <li class="<?=$name['position']?>"><a href="#<?=$link?>" ><img class="tab-logo" src="<?=$name['logo']?>"><?=$name['name']?></a></li>
            <?php endforeach;?>
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