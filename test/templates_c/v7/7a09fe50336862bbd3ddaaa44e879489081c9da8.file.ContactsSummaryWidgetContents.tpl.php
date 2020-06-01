<?php /* Smarty version Smarty-3.1.7, created on 2020-05-24 15:53:52
         compiled from "/var/www/html/crm/includes/runtime/../../layouts/v7/modules/Vtiger/ContactsSummaryWidgetContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:5181140665eca98903f7c61-53453345%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7a09fe50336862bbd3ddaaa44e879489081c9da8' => 
    array (
      0 => '/var/www/html/crm/includes/runtime/../../layouts/v7/modules/Vtiger/ContactsSummaryWidgetContents.tpl',
      1 => 1579841001,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5181140665eca98903f7c61-53453345',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RELATED_RECORDS' => 0,
    'RELATED_RECORD' => 0,
    'MODULE' => 0,
    'RELATED_MODULE' => 0,
    'NUMBER_OF_RECORDS' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5eca9890409aa',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5eca9890409aa')) {function content_5eca9890409aa($_smarty_tpl) {?>
<div class="relatedContacts container-fluid"><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><div class="recentActivitiesContainer row"><ul class="unstyled"><li><div class=""><div class="textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo Vtiger_Util_Helper::getRecordName($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id'));?>
"><?php echo Vtiger_Util_Helper::getRecordName($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id'));?>
</a></div><div><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('email');?>
</div><div class="textOverflowEllipsis" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('phone');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('phone');?>
</div></div></li></ul></div><?php } ?><?php $_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS'] = new Smarty_variable(count($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS']->value==5){?><div class="row"><div class="pull-right"><a href="javascript:void(0)" class="moreRecentContacts cursorPointer"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></div></div><?php }?></div>
<?php }} ?>