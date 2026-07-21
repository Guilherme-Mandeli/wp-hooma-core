<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="hooma-canvas-view" id="view-package-details">
    <div class="hooma-detail-view-header">
        <div class="hooma-detail-title-row">
            <h2 class="hooma-detail-title" id="pkg-detail-name">Package Name</h2>
            <span class="hooma-badge-pill pill-active" id="pkg-detail-type-pill"><span class="dashicons dashicons-editor-code"></span> Javascript</span>
        </div>
    </div>
    
    <div class="hooma-detail-grid">
        <div class="hooma-detail-sidebar">
            <div class="hooma-detail-card">
                <h3><?php _e('Package Info', 'hooma'); ?></h3>
                <table class="hooma-detail-card-table">
                    <tr>
                        <th><?php _e('Version', 'hooma'); ?>:</th>
                        <td id="pkg-detail-version"><code>1.0.0</code></td>
                    </tr>
                    <tr id="row-pkg-author">
                        <th><?php _e('Author', 'hooma'); ?>:</th>
                        <td id="pkg-detail-author"></td>
                    </tr>
                    <tr id="row-pkg-license">
                        <th><?php _e('License', 'hooma'); ?>:</th>
                        <td id="pkg-detail-license"><code>MIT</code></td>
                    </tr>
                </table>
                
                <div class="hooma-sidebar-actions" style="margin-top:15px; display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="button" id="pkg-detail-homepage-btn" style="display:none; align-items:center; gap:6px;"><span><?php _e('Homepage', 'hooma'); ?></span><span class="dashicons dashicons-external" style="font-size:15px; width:15px; height:15px; margin:0; transform: translateY(-6px);"></span></a>
                    <a href="#" target="_blank" rel="noopener noreferrer" class="button" id="pkg-detail-docs-btn" style="display:none; align-items:center; gap:6px;"><span><?php _e('Official Docs', 'hooma'); ?></span><span class="dashicons dashicons-external" style="font-size:15px; width:15px; height:15px; margin:0; transform: translateY(-6px);"></span></a>
                </div>
            </div>
            
            <div class="hooma-detail-card" id="card-pkg-compatibility">
                <h3><?php _e('Compatible Services', 'hooma'); ?></h3>
                <div id="pkg-detail-compatibility-list" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;"></div>
            </div>
            
            <div class="hooma-detail-card">
                <h3><?php _e('Actions', 'hooma'); ?></h3>
                <a href="#" class="button button-link-delete" id="pkg-detail-delete-btn" style="color:#d63638; display:block; text-align:center;"><?php _e('Delete Package', 'hooma'); ?></a>
            </div>
        </div>
        
        <div class="hooma-detail-main" style="border:1px solid #e0e0e0; overflow:hidden;">
            <div class="hooma-detail-tabs-nav">
                <button class="hooma-detail-tab-btn active" id="btn-pkg-tab-readme" onclick="hoomaSwitchPkgTab(event, 'readme')"><?php _e('Readme', 'hooma'); ?></button>
                <button class="hooma-detail-tab-btn" id="btn-pkg-tab-examples" onclick="hoomaSwitchPkgTab(event, 'examples')"><?php _e('Examples & Snippets', 'hooma'); ?></button>
                <button class="hooma-detail-tab-btn" id="btn-pkg-tab-docs" onclick="hoomaSwitchPkgTab(event, 'docs')"><?php _e('Documentation', 'hooma'); ?></button>
            </div>
            
            <div class="hooma-detail-tab-pane active" id="pkg-pane-readme">
                <div class="hooma-markdown-body" id="pkg-readme-viewer"></div>
            </div>
            
            <div class="hooma-detail-tab-pane" id="pkg-pane-examples" style="padding:0;">
                <div class="hooma-examples-explorer" style="border:none; min-height:400px; border-radius:0;">
                    <div class="hooma-examples-list-panel" id="pkg-examples-list" style="border-right:1px solid #e0e0e0;"></div>
                    <div class="hooma-code-viewer-panel">
                        <div class="hooma-code-viewer-header" id="pkg-examples-header" style="background:#f6f7f7;">
                            <span class="description"><?php _e('Select a file to view its content', 'hooma'); ?></span>
                        </div>
                        <pre class="hooma-code-pre" style="border:none; border-radius:0;"><code id="pkg-examples-body"></code></pre>
                    </div>
                </div>
            </div>
            
            <div class="hooma-detail-tab-pane" id="pkg-pane-docs" style="padding:0;">
                <div class="hooma-examples-explorer" style="border:none; min-height:400px; border-radius:0;">
                    <div class="hooma-examples-list-panel" id="pkg-docs-list" style="border-right:1px solid #e0e0e0;"></div>
                    <div class="hooma-code-viewer-panel" style="background:#fff;">
                        <div class="hooma-code-viewer-header" id="pkg-docs-header" style="background:#f6f7f7;">
                            <span class="description"><?php _e('Select a document to view its content', 'hooma'); ?></span>
                        </div>
                        <div class="hooma-markdown-body" id="pkg-docs-body" style="padding:20px; overflow-y:auto; flex-grow:1; box-sizing:border-box;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
