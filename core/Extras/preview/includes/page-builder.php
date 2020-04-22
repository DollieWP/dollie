<?php

// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);

?>
<div class="wrap livepreview">
	<?php ?>
	<div class="livepreviewpro-page-header">
		<div class="livepreviewpro-title"><i class="fa fa-cubes"></i><?php esc_html_e('LivePreview Builder', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
	</div>
	<div class="livepreviewpro-messages" id="livepreviewpro-messages">
	</div>
	<!-- livepreviewpro app -->
	<div id="livepreviewpro-app-builder" class="livepreviewpro-app" style="display:none;">
		<input id="livepreviewpro-load-config-from-file" type="file" style="display:none;" />
		<div class="livepreviewpro-loader-wrap">
			<div class="livepreviewpro-loader">
				<div class="livepreviewpro-loader-bar"></div>
				<div class="livepreviewpro-loader-bar"></div>
				<div class="livepreviewpro-loader-bar"></div>
				<div class="livepreviewpro-loader-bar"></div>
			</div>
		</div>
		<div class="livepreviewpro-wrap">
			<div class="livepreviewpro-workplace">
				<div class="livepreviewpro-main-menu">
					<div class="livepreviewpro-left-panel">
						<a class="livepreviewpro-version-lite" href="https://1.envato.market/g7zKO" al-if="appData.plan=='lite'"><?php esc_html_e('Buy Pro version', LIVEPREVIEWPRO_PLUGIN_NAME); ?></a>
						<a class="livepreviewpro-version-pro" href="#" al-if="appData.plan=='pro'"><?php esc_html_e('Pro Version', LIVEPREVIEWPRO_PLUGIN_NAME); ?></a>
					</div>
					<div class="livepreviewpro-right-panel">
						<div class="livepreviewpro-item" al-if="appData.plan=='pro'">
							<i class="fa fa-bars"></i>
							<div class="livepreviewpro-menu-list">
								<a href="#" al-on.click="appData.fn.loadConfigFromFile(appData)"><i class="fa fa-upload"></i><?php esc_html_e('Load Config From File', LIVEPREVIEWPRO_PLUGIN_NAME); ?></a>
								<a href="#" al-on.click="appData.fn.saveConfigToFile(appData)"><i class="fa fa-download"></i><?php esc_html_e('Save Config To File', LIVEPREVIEWPRO_PLUGIN_NAME); ?></a>
							</div>
						</div>
						<div class="livepreviewpro-item" al-on.click="appData.fn.toggleFullscreen(appData)">
							<i class="fa" al-attr.class.fa-arrows-alt="!appData.ui.fullscreen" al-attr.class.fa-window-restore="appData.ui.fullscreen"></i>
						</div>
					</div>
				</div>
				<div class="livepreviewpro-main-tabs livepreviewpro-clear-fix">
					<div class="livepreviewpro-tab" al-attr.class.livepreviewpro-active="appData.ui.tabs.general" al-on.click="appData.fn.onTab(appData, 'general')"><?php esc_html_e('General', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
					<div class="livepreviewpro-tab" al-attr.class.livepreviewpro-active="appData.ui.tabs.items" al-on.click="appData.fn.onTab(appData, 'items')"><?php esc_html_e('Items', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
					<div class="livepreviewpro-tab">
						<div class="livepreviewpro-button livepreviewpro-green" al-on.click="appData.fn.preview(appData);" title="<?php esc_html_e('The config should be saved before preview', LIVEPREVIEWPRO_PLUGIN_NAME); ?>" al-if="appData.config.page"><?php esc_html_e('Preview', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
						<div class="livepreviewpro-button livepreviewpro-blue" al-on.click="appData.fn.saveConfig(appData);"><?php esc_html_e('Save', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
					</div>
				</div>
				<div class="livepreviewpro-main-data">
					<div class="livepreviewpro-section" al-attr.class.livepreviewpro-active="appData.ui.tabs.general">
						<div class="livepreviewpro-stage">
							<div class="livepreviewpro-main-panel">
								<div class="livepreviewpro-data livepreviewpro-active">
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-info"><?php esc_html_e('General settings', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Specifies a theme', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Theme', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<select class="livepreviewpro-select livepreviewpro-capitalize" al-select="appData.config.theme">
											<option al-option="null"><?php esc_html_e('none', LIVEPREVIEWPRO_PLUGIN_NAME); ?></option>
											<option al-repeat="theme in appData.themes" al-option="theme.id">{{theme.title}}</option>
										</select>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets a logo image (jpeg or png format)', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Logo image', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div class="livepreviewpro-input-group">
											<div class="livepreviewpro-input-group-cell">
												<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.config.logo.url" placeholder="<?php esc_html_e('Select an image', LIVEPREVIEWPRO_PLUGIN_NAME); ?>">
											</div>
											<div class="livepreviewpro-input-group-cell livepreviewpro-pinch">
												<div class="livepreviewpro-btn livepreviewpro-default livepreviewpro-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.config.logo)" title="<?php esc_html_e('Select an image', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"><span><i class="fa fa-folder-open-o"></i></span></div>
											</div>
										</div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets a logo URL', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Logo URL', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div class="livepreviewpro-input-group">
											<div class="livepreviewpro-input-group-cell">
												<input class="livepreviewpro-text" type="text" al-text="appData.config.logo.href" placeholder="<?php esc_html_e('Set a logo URL', LIVEPREVIEWPRO_PLUGIN_NAME); ?>">
											</div>
										</div>
										<div class="livepreviewpro-input-group">
											<div class="livepreviewpro-input-group-cell livepreviewpro-pinch">
												<div al-checkbox="appData.config.logo.blank"></div>
											</div>
											<div class="livepreviewpro-input-group-cell">
												<?php esc_html_e('Open in a new window', LIVEPREVIEWPRO_PLUGIN_NAME); ?>
											</div>
										</div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable the product list', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Product list', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div al-toggle="appData.config.productList"></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable responsive devices', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Responsive devices', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div al-toggle="appData.config.responsiveDevices"></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Specifies the default responsive device', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Default responsive device', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div class="livepreviewpro-select" al-devices="appData.config.responsiveDevice"></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable buy button', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Buy button', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div al-toggle="appData.config.buyButton"></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Set the buy button text', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Buy button text', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<input class="livepreviewpro-text" type="text" al-text="appData.config.buyButtonText" placeholder="<?php esc_html_e('Set a buy button text', LIVEPREVIEWPRO_PLUGIN_NAME); ?>">
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable close iframe button', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Close iframe button', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div al-toggle="appData.config.closeIframe"></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable the main preload overlay', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Main preload overlay', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<div al-toggle="appData.config.preload"></div>
									</div>
									
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-info"><?php esc_html_e('Page settings', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
									</div>
									<div class="livepreviewpro-control">
										<div class="livepreviewpro-helper" title="<?php esc_html_e('Specifies a demo page', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
										<div class="livepreviewpro-label"><?php esc_html_e('Demo page', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<select class="livepreviewpro-select livepreviewpro-normal" al-select="appData.config.page">
											<option al-option="null"><?php esc_html_e('None', LIVEPREVIEWPRO_PLUGIN_NAME); ?></option>
											<option al-repeat="page in appData.pages" al-option="page.id">{{page.title}}</option>
										</select>
									</div>
									<div class="livepreviewpro-control" al-if="appData.page">
										<div class="livepreviewpro-label"><?php esc_html_e('Demo page url', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										<a target="_blank" href="{{appData.fn.getPageDemoUrl(appData)}}">{{appData.fn.getPageDemoUrl(appData)}}</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="livepreviewpro-section" al-attr.class.livepreviewpro-active="appData.ui.tabs.items">
						<div class="livepreviewpro-stage">
							<div class="livepreviewpro-sidebar-panel" al-attr.class.livepreviewpro-hidden="!appData.ui.sidebar" al-style.width="appData.ui.sidebarWidth">
								<div class="livepreviewpro-data">
									<div class="livepreviewpro-items-wrap">
										<div class="livepreviewpro-items-toolbar">
											<div class="livepreviewpro-left-panel">
												<i class="fa fa-plus-circle" al-on.click="appData.fn.addItem(appData)" title="<?php esc_html_e('add item', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												<span al-if="appData.ui.activeItem != null">
												<i class="fa fa-clone" al-on.click="appData.fn.copyItem(appData)" title="<?php esc_html_e('copy', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												<i class="fa fa-arrow-up fa-top" al-on.click="appData.fn.updownItem(appData, 'start')" title="<?php esc_html_e('move to the start', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												<i class="fa fa-arrow-up" al-on.click="appData.fn.updownItem(appData, 'up')" title="<?php esc_html_e('move up', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												<i class="fa fa-arrow-down" al-on.click="appData.fn.updownItem(appData, 'down')" title="<?php esc_html_e('move down', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												<i class="fa fa-arrow-down fa-bottom" al-on.click="appData.fn.updownItem(appData, 'end')" title="<?php esc_html_e('move to the end', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
												</span>
											</div>
											<div class="livepreviewpro-right-panel">
												<i class="fa fa-trash" al-if="appData.ui.activeItem != null" al-on.click="appData.fn.deleteItem(appData)" title="<?php esc_html_e('delete', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
											</div>
										</div>
										<div class="livepreviewpro-items-list">
										<div class="livepreviewpro-item"
										 tabindex="0"
										 al-attr.class.livepreviewpro-active="appData.fn.isItemActive(appData, item)"
										 al-on.click="appData.fn.onItemClick(appData, item)"
										 al-on.keydown="appData.fn.onItemKeyDown(appData, item, $event)"
										 al-repeat="item in appData.config.items"
										 >
											<i class="fa fa-cube"></i>
											<div class="livepreviewpro-label">{{item.title ? item.title : '...'}}</div>
											<div class="livepreviewpro-actions">
												<i class="fa" al-attr.class.fa-toggle-on="item.active" al-attr.class.fa-toggle-off="!item.active" al-on.click="appData.fn.toggleItemActive(appData, item)" title="<?php esc_html_e('enable/disable', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></i>
											</div>
										</div>
										</div>
									</div>
								</div>
								<div class="livepreviewpro-sidebar-resizer" al-on.mousedown="appData.fn.onSidebarResizeStart(appData, $event)">
									<div class="livepreviewpro-sidebar-hide" al-on.click="appData.fn.toggleSidebarPanel(appData)">
										<i class="fa fa-chevron-right" al-if="!appData.ui.sidebar"></i>
										<i class="fa fa-chevron-left" al-if="appData.ui.sidebar"></i>
									</div>
								</div>
							</div>
							<div class="livepreviewpro-main-panel">
								<div class="livepreviewpro-data livepreviewpro-active">
									<div al-if="appData.ui.activeItem == null">
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-info"><?php esc_html_e('Please, select an item to view settings', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
										</div>
									</div>
									<div al-if="appData.ui.activeItem != null">
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable item', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Enable item', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<div al-toggle="appData.ui.activeItem.active"></div>
										</div>
										
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item id (allow numbers, chars & specials: "_","-")', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Id', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<div class="livepreviewpro-input-group livepreviewpro-long">
												<div class="livepreviewpro-input-group-cell">
													<input class="livepreviewpro-text livepreviewpro-long" type="text" al-textid="appData.ui.activeItem.id">
												</div>
												<div class="livepreviewpro-input-group-cell livepreviewpro-pinch">
													<div class="livepreviewpro-btn livepreviewpro-default livepreviewpro-no-bl livepreviewpro-no-br" al-on.click="appData.fn.preview(appData, appData.ui.activeItem.id)" title="<?php esc_html_e('Preview item, the config should be saved before preview', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"><span><i class="fa fa-external-link"></i></span></div>
												</div>
												<div class="livepreviewpro-input-group-cell livepreviewpro-pinch">
													<div class="livepreviewpro-btn livepreviewpro-default" al-on.click="appData.fn.generateId(appData, appData.rootScope, appData.ui.activeItem)" title="<?php esc_html_e('Generate a new ID', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"><span><i class="fa fa-refresh"></i></span></div>
												</div>
											</div>
										</div>
										
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item title', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Title', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.title">
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets a thumb image (jpeg or png format)', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Thumb image (590x300 recommend)', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<div class="livepreviewpro-input-group livepreviewpro-long">
												<div class="livepreviewpro-input-group-cell">
													<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.thumb.url" placeholder="<?php esc_html_e('Select an image', LIVEPREVIEWPRO_PLUGIN_NAME); ?>">
												</div>
												<div class="livepreviewpro-input-group-cell livepreviewpro-pinch">
													<div class="livepreviewpro-btn livepreviewpro-default livepreviewpro-no-bl" al-on.click="appData.fn.selectImage(appData, appData.rootScope, appData.ui.activeItem.thumb)" title="<?php esc_html_e('Select an image', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"><span><i class="fa fa-folder-open-o"></i></span></div>
												</div>
											</div>
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item url to a demo preview page', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Demo url', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.url">
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item buy url', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Buy url', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.buy">
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item badge text', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Badge', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.badge">
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item tag text', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Tag', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-text="appData.ui.activeItem.tag">
										</div>
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Sets an item year', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Year', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<input class="livepreviewpro-text livepreviewpro-long" type="text" al-integer="appData.ui.activeItem.year">
										</div>
										
										<div class="livepreviewpro-control">
											<div class="livepreviewpro-helper" title="<?php esc_html_e('Enable/disable preload overlay when iframe content is loading', LIVEPREVIEWPRO_PLUGIN_NAME); ?>"></div>
											<div class="livepreviewpro-label"><?php esc_html_e('Preload overlay when iframe loading', LIVEPREVIEWPRO_PLUGIN_NAME); ?></div>
											<div al-toggle="appData.ui.activeItem.preload"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /end livepreviewpro app -->
</div>