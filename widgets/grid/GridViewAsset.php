<?php
/**
 * @copyright Copyright (c) 2015 Ragnarok-Exiler
 * @license https://github.com/ragnarok-exiler/yii2-adminlte/blob/master/LICENSE
 * @link http://adminlte.yiister.ru
 */

namespace ragnarokexiler\adminlte\widgets\grid;

class GridViewAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins/datatables';
    public $css = [
        'dataTables.bootstrap.css',
    ];
    public $js = [];
    public $depends = [
        'ragnarokexiler\adminlte\assets\Asset',
    ];
}
