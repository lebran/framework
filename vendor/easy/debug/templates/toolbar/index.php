<?=$files?>

<div id="debug-toolbar">
    <div id="debug-toolbar-header">   
        <ul>
            <li class="left-tab"><?=$html->img('img/logo1.png', array('class' => 'logo'))?></li>
            <li class="right-tab" id="close"><a class="close" href="#close" ><?=$html->img('img/cancel.png', array('class' => 'tab-logo'))?></a></li>
            <?php foreach ($tab_keys as $link => $name): ?>
            <li class="<?=$name['position']?>"><a href="#<?=$link?>"><?=$html->img($name['logo'], array('class' => 'tab-logo'))?><?=$name['name']?></a></li>
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