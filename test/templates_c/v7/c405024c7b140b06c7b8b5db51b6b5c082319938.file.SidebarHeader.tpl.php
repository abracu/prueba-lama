<?php /* Smarty version Smarty-3.1.7, created on 2020-01-24 05:13:02
         compiled from "/var/www/html/crm/includes/runtime/../../layouts/v7/modules/Settings/Vtiger/SidebarHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:13944624515e2a7a0c557cd8-86020165%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c405024c7b140b06c7b8b5db51b6b5c082319938' => 
    array (
      0 => '/var/www/html/crm/includes/runtime/../../layouts/v7/modules/Settings/Vtiger/SidebarHeader.tpl',
      1 => 1579842317,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '13944624515e2a7a0c557cd8-86020165',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5e2a7a0c56528',
  'variables' => 
  array (
    'SELECTED_MENU_CATEGORY' => 0,
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5e2a7a0c56528')) {function content_5e2a7a0c56528($_smarty_tpl) {?>

<?php $_smarty_tpl->tpl_vars['APP_IMAGE_MAP'] = new Smarty_variable(Vtiger_MenuStructure_Model::getAppIcons(), null, 0);?>
<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-<?php echo $_smarty_tpl->tpl_vars['SELECTED_MENU_CATEGORY']->value;?>
">
    <div class="row" title="<?php echo vtranslate("LBL_SETTINGS",$_smarty_tpl->tpl_vars['MODULE']->value);?>
">
        <span class="app-indicator-icon fa fa-cog"></span>
    </div>
</div>
    
<?php echo $_smarty_tpl->getSubTemplate ("modules/Vtiger/partials/SidebarAppMenu.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>