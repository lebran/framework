<?=$files?>

<div id="debug-toolbar">
    <div class="ui-resizable-handle ui-resizable-n" id="n-element"></div>
    <div id="debug-toolbar-header">
        <ul>
            <li class="left-tab">
                <?=$html->img('img/logo.png', array('class' => 'logo'))?>
            </li>
            <li class="right-tab" id="close">
                <a class="close" href="#close" >
                    <?=$html->img('img/cancel.png', array('class' => 'tab-logo'))?>
                </a>
            </li>
            <li class="right-tab" >
                <a id="time">
                    <?=$html->img('img/memory.png', array('class' => 'tab-logo'))?>
                    <?=$memory?>
                </a>
            </li>
            <li class="right-tab" >
                <a id="time">
                    <?=$html->img('img/time.png', array('class' => 'tab-logo'))?>
                    <?=$time?>
                </a>
            </li>
            <?php foreach ($tab_keys as $link => $name): ?>
            <li class="<?=$name['position']?>-tab">
                <a href="#<?=$link?>">
                    <?=$html->img($name['logo'], array('class' => 'tab-logo'))?>
                    <?=$name['name']?>
                    <?php if(isset($name['info'])):?><span class="count"><?=$name['info']?></span><?php endif;?>
                </a>
            </li>
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