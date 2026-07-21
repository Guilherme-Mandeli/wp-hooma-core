<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="hooma-canvas-view" id="view-module-details">
    <div class="hooma-detail-view-header">
        <div class="hooma-detail-title-row">
            <h2 class="hooma-detail-title" id="mod-detail-name">Module Name</h2>
            <span class="hooma-badge-pill" id="mod-detail-status-pill">Active</span>
        </div>
    </div>
    
    <div class="hooma-detail-grid">
        <div class="hooma-detail-sidebar">
            <div class="hooma-detail-card">
                <h3><?php _e('Metadata', 'hooma'); ?></h3>
                <table class="hooma-detail-card-table">
                    <tr>
                        <th><?php _e('ID / Slug', 'hooma'); ?>:</th>
                        <td id="mod-detail-slug"><code>slug</code></td>
                    </tr>
                    <tr>
                        <th><?php _e('Version', 'hooma'); ?>:</th>
                        <td id="mod-detail-version">1.0.0</td>
                    </tr>
                </table>
            </div>
            
            <div class="hooma-detail-card">
                <h3><?php _e('Actions', 'hooma'); ?></h3>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="#" class="button button-primary" id="mod-detail-open-btn" style="text-align:center;"><?php _e('Open', 'hooma'); ?></a>
                    <a href="#" class="button button-secondary" id="mod-detail-toggle-btn" style="text-align:center;">Deactivate</a>
                    <?php if (current_user_can('manage_options')) : ?>
                        <a href="#" class="button button-link-delete" id="mod-detail-delete-btn" style="color:#d63638; text-align:center;">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="hooma-detail-main" style="border:1px solid #e0e0e0; overflow:hidden;">
            <div class="hooma-detail-tabs-nav">
                <button class="hooma-detail-tab-btn active" id="btn-mod-tab-readme" onclick="hoomaSwitchModTab(event, 'readme')"><?php _e('Overview', 'hooma'); ?></button>
                <button class="hooma-detail-tab-btn" id="btn-mod-tab-docs" onclick="hoomaSwitchModTab(event, 'docs')"><?php _e('Documentation', 'hooma'); ?></button>
            </div>
            
            <div class="hooma-detail-tab-pane active" id="mod-pane-readme" style="padding:20px; overflow-y:auto;">
                <h3 style="margin-top:0; font-size:14px; font-weight:600; color:#1d2327; border-bottom:1px solid #f0f0f1; padding-bottom:8px; margin-bottom:12px;"><?php _e('Description', 'hooma'); ?></h3>
                <p id="mod-detail-description" style="font-size:14px; line-height:1.6; color:#50575e; margin-bottom: 20px;"></p>
                <hr id="mod-detail-readme-separator" style="margin: 20px 0; border: 0; border-top: 1px solid #dcdcde; display: none;">
                <div id="mod-detail-readme" class="hooma-markdown-body" style="display: none;"></div>
            </div>
            
            <div class="hooma-detail-tab-pane" id="mod-pane-docs" style="padding:0;">
                <div class="hooma-examples-explorer" style="border:none; min-height:400px; border-radius:0;">
                    <div class="hooma-examples-list-panel" id="mod-docs-list" style="border-right:1px solid #e0e0e0;"></div>
                    <div class="hooma-code-viewer-panel" style="background:#fff;">
                        <div class="hooma-code-viewer-header" id="mod-docs-header" style="background:#f6f7f7;">
                            <span class="description"><?php _e('Select a document to view its content', 'hooma'); ?></span>
                        </div>
                        <div class="hooma-markdown-body" id="mod-docs-body" style="padding:20px; overflow-y:auto; flex-grow:1; box-sizing:border-box;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
