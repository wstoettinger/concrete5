<?
defined('C5_EXECUTE') or die("Access Denied.");
$html = Loader::helper('html');
$dh = Loader::helper('concrete/dashboard');
$ihm = Loader::helper('concrete/interface/menu');
$valt = Loader::helper('validation/token');
$token = '&' . $valt->getParameter();
$logouttoken = Loader::helper('validation/token')->generate('logout');
$cID = $c->getCollectionID();

$workflowList = PageWorkflowProgress::getList($c);

$canViewToolbar = $cp->canViewToolbar();

if (isset($cp) && $canViewToolbar && (!$dh->inDashboard())) { 

	$u = new User();
	$username = $u->getUserName();
	$vo = $c->getVersionObject();
	$pageInUseBySomeoneElse = false;

	if ($c->isCheckedOut()) {
		if (!$c->isCheckedOutByMe()) {
			$pageInUseBySomeoneElse = true;
		}
	}


	if ($c->isEditMode()) { 
		if ($vo->isNew()) {
			$publishToggle = '#ccm-exit-edit-mode-comment';
		} else {
			$publishToggle = '#ccm-exit-edit-mode-direct';
		}
	} else {
		$publishToggle = '#ccm-toolbar-menu-page-edit';
	}

	?>

	<div id="ccm-page-controls-wrapper" class="ccm-ui">
		<div id="ccm-toolbar">
			<ul>
				<li class="ccm-logo pull-left"><span><?=Loader::helper('concrete/interface')->getToolbarLogoSRC()?></span></li>
				<? if ($c->isMasterCollection()) { ?>
					<li class="pull-left"><a href="<?=View::url('/dashboard/pages/types')?>"><i class="glyphicon glyphicon-arrow-left"></i></a>
				<? } ?>
				<? if (!$pageInUseBySomeoneElse && $c->getCollectionPointerID() == 0) { ?>
				<li class="<? if ($c->isEditMode()) { ?> ccm-toolbar-page-edit-mode-active <? } ?> ccm-toolbar-page-edit pull-left"><a data-toggle="ccm-toolbar-hover-menu" href="<? if (!$c->isEditMode()) { ?><?=DIR_REL?>/<?=DISPATCHER_FILENAME?>?cID=<?=$c->getCollectionID()?>&ctask=check-out<?=$token?><? } else { ?>javascript:void(0);<? } ?>"><i class="glyphicon glyphicon-pencil"></i></a></li>
				<li class="pull-left"><a href="#" data-launch-panel="properties"><i class="glyphicon glyphicon-cog"></i></a>

				</li>
				<? }

				if ($cp->canEditPageContents() && (!$pageInUseBySomeoneElse)) { ?>
					<li class="ccm-toolbar-add pull-left">
						<? if ($c->isEditMode()) { ?>
							<a class="dialog-launch" <? if ($_COOKIE['ccmLoadAddBlockWindow'] && $c->isEditMode()) { ?>dialog-on-direct-close="window.location.href='<?=DIR_REL?>/<?=DISPATCHER_FILENAME?>?cID=<?=$c->getCollectionID()?>&ctask=check-in<?=$token?>'"<? } ?> data-dialog="add-block" title="<?=t('Add Block')?>" dialog-width="660" dialog-height="430" dialog-modal="false" dialog-title="<?=t('Add Block')?>" href="<?=REL_DIR_FILES_TOOLS_REQUIRED?>/pages/add_block?cID=<?=$c->getCollectionID()?>"><i class="glyphicon glyphicon-plus"></i></a>
						<? } else { ?>
							<a title="<?=t('Add Block')?>" href="<?=DIR_REL?>/<?=DISPATCHER_FILENAME?>?cID=<?=$cID?>&ctask=check-out-add-block<?=$token?>"><i class="glyphicon glyphicon-plus"></i></a>
							<? } ?>
					</li>
				<? } 

					
				$items = $ihm->getPageHeaderMenuItems('left');
				foreach($items as $ih) {
					$cnt = $ih->getController(); 
					if ($cnt->displayItem()) {
					?>
						<li class="pull-left"><?=$cnt->getMenuLinkHTML()?></li>
					<?
					}
				}
				
				if (Loader::helper('concrete/interface')->showWhiteLabelMessage()) { ?>
					<li class="pull-left" id="ccm-white-label-message"><?=t('Powered by <a href="%s">concrete5</a>.', CONCRETE5_ORG_URL)?></li>
				<? }?>

				<li class="pull-right"><a href="<?=$this->url('/dashboard')?>" data-launch-panel="dashboard"><i class="glyphicon glyphicon-th-large"></i></a>

				<li class="ccm-toolbar-page-menu pull-right"><a href="<?=$this->url('/dashboard')?>" data-toggle="ccm-toolbar-hover-menu"><i class="glyphicon glyphicon-list-alt"></i></a>

				<?
				print $dh->getIntelligentSearchMenu();
				?>

					</li>
				<li class="ccm-toolbar-search pull-right"><i class="glyphicon glyphicon-search"></i> <input type="search" id="ccm-nav-intelligent-search" tabindex="1" /></li>
				<?
				$items = $ihm->getPageHeaderMenuItems('right');
				foreach($items as $ih) {
					$cnt = $ih->getController(); 
					if ($cnt->displayItem()) {
					?>
						<li class="pull-right"><?=$cnt->getMenuLinkHTML()?></li>
					<?
					}
				}

				?>

			</ul>

		</div>

	<? if ($pageInUseBySomeoneElse) { ?>
		<div id="ccm-page-status-bar">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button> <span><?= t("%s is currently editing this page.", $c->getCollectionCheckedOutUserName())?></span></div>
		</div>
	<? } else { ?>

	<? if ($c->getCollectionPointerID() > 0) { ?>

		<div id="ccm-page-status-bar">
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<span><?= t("This page is an alias of one that actually appears elsewhere.")?></span>
				<div class="ccm-page-status-bar-buttons">
					<a href="<?=DIR_REL . "/" . DISPATCHER_FILENAME . "?cID=" . $c->getCollectionID()?>" class="btn btn-mini"><?=t('View/Edit Original')?></a>
					<? if ($canApprovePageVersions) { ?>
						<a href="<?=DIR_REL . "/" . DISPATCHER_FILENAME . "?cID=" . $c->getCollectionPointerOriginalID() . "&ctask=remove-alias" . $token?>" class="btn btn-mini btn-danger"><?=t('Remove Alias')?></a>
					<? } ?>
				</div>
			</div>
		</div>

	<? }

	if ($c->isMasterCollection()) { ?>

		<div id="ccm-page-status-bar">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button> <span><?= t('Page Defaults for %s Page Type. All edits take effect immediately.', $c->getCollectionTypeName()) ?></span></div>
		</div>

	<? }
	
	$hasPendingPageApproval = false;
	
	if ($canViewToolbar) { ?>
		<? if (is_array($workflowList) && count($workflowList) > 0) { ?>
			<div id="ccm-page-status-bar">
			<? foreach($workflowList as $i => $wl) { ?>
				<? $wr = $wl->getWorkflowRequestObject(); 
				$wrk = $wr->getWorkflowRequestPermissionKeyObject(); 
				if ($wrk->getPermissionKeyHandle() == 'approve_page_versions') {
					$hasPendingPageApproval = true;
				}
				?>
				<? $wf = $wl->getWorkflowObject(); ?>
				<form method="post" action="<?=$wl->getWorkflowProgressFormAction()?>" id="ccm-status-bar-form-<?=$i?>" class="ccm-status-bar-ajax-form">
					<div class="alert alert-<?=$wr->getWorkflowRequestStyleClass()?>"><button type="button" class="close" data-dismiss="alert">×</button> <span><?=$wf->getWorkflowProgressCurrentDescription($wl)?></span>
					<? $actions = $wl->getWorkflowProgressActions(); ?>
					<? if (count($actions) > 0) { ?>
						<div class="ccm-page-status-bar-buttons">
						<? foreach($actions as $act) { ?>
							<? if ($act->getWorkflowProgressActionURL() != '') { ?>
								<a href="<?=$act->getWorkflowProgressActionURL()?>" 
							<? } else { ?>
								<button type="submit" name="action_<?=$act->getWorkflowProgressActionTask()?>" 
							<? } ?>

							<? if (count($act->getWorkflowProgressActionExtraButtonParameters()) > 0) { ?>
								<? foreach($act->getWorkflowProgressActionExtraButtonParameters() as $key => $value) { ?>
									<?=$key?>="<?=$value?>" 
								<? } ?>
							<? } ?>

							 class="btn btn-mini <?=$act->getWorkflowProgressActionStyleClass()?>"><?=$act->getWorkflowProgressActionStyleInnerButtonLeftHTML()?> <?=$act->getWorkflowProgressActionLabel()?> <?=$act->getWorkflowProgressActionStyleInnerButtonRightHTML()?>
							<? if ($act->getWorkflowProgressActionURL() != '') { ?>
								</a>
							<? } else { ?>
								</button>
							<? } ?>
						<? } ?>
						</div>
					<? } ?>	
					</div>				
				</form>
				<? } ?>
			</div>
		<? } ?>
	<? }

	if (!$c->getCollectionPointerID() && !$hasPendingPageApproval) {
		if (is_object($vo)) {
			if (!$vo->isApproved() && !$c->isEditMode()) { ?>

			<div id="ccm-page-status-bar">
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<span><?= t("This page is pending approval.")?></span>
					<? if ($canApprovePageVersions && !$c->isCheckedOut()) { ?>
					<div class="ccm-page-status-bar-buttons">
						<?
						$pk = PagePermissionKey::getByHandle('approve_page_versions');
						$pk->setPermissionObject($c);
						$pa = $pk->getPermissionAccessObject();
						if (is_object($pa)) {
							if (count($pa->getWorkflows()) > 0) {
								$appLabel = t('Submit for Approval');
							}
						}
						if (!$appLabel) {
							$appLabel = t('Approve Version');
						}
						?>
						<a href="<?=DIR_REL . "/" . DISPATCHER_FILENAME . "?cID=" . $c->getCollectionID() . "&ctask=approve-recent" . $token?>" class="btn btn-mini"><?=$appLabel?> <i class="glyphicon glyphicon-thumbs-up"></i></a>
					</div>
					<? } ?>
				</div>
			</div>
			<? }
		}
	} ?>	

	<? } ?>	
	</div>

<? }