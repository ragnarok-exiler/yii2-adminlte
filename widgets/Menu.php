<?php
/**
 * @copyright Copyright (c) 2015 Ragnarok-Exiler
 * @license https://github.com/ragnarok-exiler/yii2-adminlte/blob/master/LICENSE
 * @link http://adminlte.yiister.ru
 */

namespace ragnarokexiler\adminlte\widgets;

use Yii;
use rmrevin\yii\fontawesome\component\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class Menu
 * @package ragnarokexiler\adminlte\widgets
 */
class Menu extends \yii\widgets\Menu
{
    /**
     * @inheritdoc
     */
    public $labelTemplate = '{label}';

    /**
     * @inheritdoc
     */
    public $linkTemplate = '<a href="{url}" class="nav-link">{icon} <p>{label}{badge}</p></a>';

    /**
     * @inheritdoc
     */
    public $submenuTemplate = "\n<ul class=\"nav nav-treeview\">\n{items}\n</ul>\n";

    /**
     * @inheritdoc
     */
    public $activateParents = true;

    /**
     * @inheritdoc
     */
    public $activeCssClass = 'menu-open';

    /**
     * @var bool whether to add menu searching or not (Searching in the menu elements).
     */
    public $menuSearching = false;

    /**
     * @inheritdoc
     */


    public function init()
    {
        if ($this->menuSearching) {
            echo Html::tag('div',
                    Html::tag('div',
                        Html::textInput('sideSerach', '', [
                            'placeholder' => 'Buscar elemento del menú...',
                            'class' => 'form-control sideSearch'
                        ]) .
                        Html::tag('span', new  Icon('search'),
                            ['class' => 'form-control-feedback kv-feedback-default']), [
                            'class' => 'has-feedback'
                        ]), [
                        'class' => 'sidebar-form'
                    ]) . Html::tag('span', '',
                    ['class' => 'menu-separator', 'style' => 'display: block; border-bottom: solid 1px #D2D6DE; margin:10px 0;']);
        }

        Html::addCssClass($this->options, 'nav nav-sidebar flex-column');
        $this->options['data']['widget'] = 'treeview';
        $this->options['data']['accordion'] = 'false';
        $this->options['role'] = 'menu';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {

        $renderedItem = parent::renderItem($item);
        if (isset($item['badge'])) {
            $badgeOptions = ArrayHelper::getValue($item, 'badgeOptions', []);
            Html::addCssClass($badgeOptions, 'badge right');
        } else {
            $badgeOptions = null;
        }

        if (isset($item['icon'])) {
            $iconOptions = ArrayHelper::getValue($item, 'iconOptions', []);

            Html::addCssClass($iconOptions, 'nav-icon');

//            if (isset($iconOptions['class'])) {
//                $iconOptions['class'] .= ' nav-icon';
//            } else {
//                $iconOptions['class'] = 'nav-icon';
//            }

            $icon = new Icon($item['icon'], $iconOptions);
        } else {

            $icon = '';
        }

        return strtr(
            $renderedItem,
            [
                '{icon}' => $icon,
                '{badge}' => (
                    isset($item['badge'])
                        ? Html::tag('span', $item['badge'], $badgeOptions)
                        : ''
                    ) . (
                    isset($item['items']) && count($item['items']) > 0
                        ? new Icon('angle-left', ['class' => 'right'])
                        : ''
                    ),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function normalizeItems($items, &$active)
    {
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (isset($items[$i]['options']['class'])) {
                $items[$i]['options']['class'] .= ' nav-item';
            } else {
                $items[$i]['options']['class'] = 'nav-item';
            }
            if (!isset($item['label'])) {
                $item['label'] = '';
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $items[$i]['label'] = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $hasActiveChild = false;
            if (isset($item['items'])) {

                $items[$i]['options']['class'] .= ' has-treeview';

                $items[$i]['items'] = $this->normalizeItems($item['items'], $hasActiveChild);
                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
            }
            if (!isset($item['active'])) {
                $activeItem = $this->isItemActive($item);
                if ($this->activateParents && $hasActiveChild || $this->activateItems && $activeItem) {
                    $active = $items[$i]['active'] = true;
                    if ($activeItem) {
                        if (isset($items[$i]['options']['class'])) {
                            $items[$i]['options']['class'] .= ' active';
                        } else {
                            $items[$i]['options']['class'] = 'active';
                        }
                    }
                } else {
                    $items[$i]['active'] = false;
                }
            } elseif ($item['active'] instanceof Closure) {
                $active = $items[$i]['active'] = call_user_func($item['active'], $item, $hasActiveChild,
                    $this->isItemActive($item), $this);
            } elseif ($item['active']) {
                $active = true;
            }
        }

        return array_values($items);
    }

    /**
     * @inheritdoc
     */
    protected function isItemActive($item)
    {
        if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $route = Yii::getAlias($item['url'][0]);
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }

            if (
                ($route == '/' && $this->route == 'site/index')
                || (substr($route, -1) == '/' && $this->route == ltrim($route, '/') . 'index')
                || ($this->route == ltrim($route, '/'))
            ) {
                unset($item['url']['#']);
                if (count($item['url']) > 1) {
                    $params = $item['url'];
                    unset($params[0]);
                    foreach ($params as $name => $value) {
                        if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                            return false;
                        }
                    }
                }

                return true;
            } else {

                return false;
            }
        }

        return false;
    }
}
