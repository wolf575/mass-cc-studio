<?php
/**
 * Mass CC Studio - Single-page frontend
 * Login + 5 tabs: Checker / Generate / Validate / BIN / Settings
 */
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mass CC Studio</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ========== Login View ========== */
    .login-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1rem;
    }
    .login-card {
      background: var(--bg-card);
      border-radius: var(--border-radius);
      padding: 2rem;
      width: 100%;
      max-width: 420px;
      box-shadow: var(--shadow-lg);
      animation: fadeInUp 0.5s var(--transition-smooth);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .login-brand h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 0.25rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .login-brand p {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin: 0;
    }
    .login-form .form-group {
      margin-bottom: 1rem;
    }
    .login-form input.form-control {
      font-family: inherit;
    }
    .login-error {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: var(--accent-danger);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 1rem;
      font-size: 0.85rem;
      display: none;
    }
    .login-error.visible {
      display: block;
      animation: slideIn 0.3s var(--transition-smooth);
    }

    /* ========== App Header ========== */
    .app-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 0.75rem;
    }
    .app-brand {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--text-primary);
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .app-brand span {
      color: var(--text-muted);
      font-size: 0.75rem;
      font-weight: 400;
      text-transform: none;
      margin-left: 0.5rem;
    }
    .app-actions {
      display: flex;
      gap: 0.5rem;
    }
    .btn-sm {
      padding: 0.4rem 1rem;
      font-size: 0.8rem;
    }

    /* ========== Tabs ========== */
    .tabs-nav {
      display: flex;
      gap: 0.25rem;
      margin-bottom: 1.25rem;
      background: var(--bg-secondary);
      border-radius: 10px;
      padding: 0.35rem;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .tabs-nav::-webkit-scrollbar {
      display: none;
    }
    .tab-btn {
      flex: 1;
      min-width: 72px;
      padding: 0.55rem 0.75rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-secondary);
      background: transparent;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all var(--transition-fast);
      white-space: nowrap;
      text-align: center;
    }
    .tab-btn:hover {
      color: var(--text-primary);
      background: rgba(255,255,255,0.04);
    }
    .tab-btn.active {
      color: var(--text-primary);
      background: var(--bg-card);
      box-shadow: var(--shadow-sm);
    }
    .tab-panel {
      display: none;
    }
    .tab-panel.active {
      display: block;
      animation: tabFadeIn 0.22s ease;
    }
    .checker-mode-wrap {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 0 0 1rem;
    }
    .mode-label {
      font-size: 0.8rem;
      color: var(--text-secondary);
    }
    .segmented {
      display: inline-flex;
      gap: 0.25rem;
      padding: 0.25rem;
      background: var(--bg-secondary);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 8px;
    }
    .seg-btn {
      padding: 0.4rem 0.9rem;
      font-size: 0.8rem;
      font-weight: 500;
      border: none;
      border-radius: 6px;
      background: transparent;
      color: var(--text-secondary);
      cursor: pointer;
      white-space: nowrap;
      transition: all var(--transition-fast);
    }
    .seg-btn:hover {
      color: var(--text-primary);
      background: rgba(255, 255, 255, 0.04);
    }
    .seg-btn.active {
      color: var(--text-primary);
      background: var(--bg-card);
      box-shadow: var(--shadow-sm);
    }
    .box-body {
      animation: none;
    }
    @keyframes tabFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* ========== Form helpers ========== */
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group:last-child {
      margin-bottom: 0;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.4rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-secondary);
    }
    .form-group input.form-control,
    .form-group select.form-control,
    .form-group textarea.form-control {
      font-family: inherit;
    }
    .form-group textarea.form-control {
      font-family: 'SF Mono', Monaco, 'Courier New', monospace;
    }
    .checkbox-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem 1.5rem;
      margin-bottom: 1rem;
    }
    .checkbox-row label {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.8rem;
      color: var(--text-secondary);
      cursor: pointer;
      user-select: none;
    }
    .checkbox-row input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: var(--accent-info);
      cursor: pointer;
    }

    /* ========== Output area ========== */
    .output-wrap {
      position: relative;
    }
    .output-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.5rem;
      margin-top: 0.75rem;
    }
    .result-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
    }
    .result-table th,
    .result-table td {
      padding: 0.6rem 0.75rem;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .result-table th {
      font-weight: 500;
      color: var(--text-muted);
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .result-table td {
      color: var(--text-primary);
    }
    .result-table tr:last-child td {
      border-bottom: none;
    }
    .result-empty {
      color: var(--text-muted);
      font-size: 0.85rem;
      padding: 1rem 0;
    }
    .result-message {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-top: 0.75rem;
    }
    .result-message.success {
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.2);
      color: var(--accent-success);
    }
    .result-message.error {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: var(--accent-danger);
    }
    .result-message.info {
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.2);
      color: var(--accent-info);
    }

    /* ========== Validate single result ========== */
    .validate-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 0.75rem;
      margin-top: 0.75rem;
    }
    .validate-card {
      background: rgba(255,255,255,0.03);
      border-radius: 8px;
      padding: 0.75rem 1rem;
    }
    .validate-card h4 {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-muted);
      margin: 0 0 0.4rem;
    }
    .validate-card .value {
      font-size: 0.9rem;
      color: var(--text-primary);
      word-break: break-all;
    }
    .validate-card .status-ok {
      color: var(--accent-success);
    }
    .validate-card .status-fail {
      color: var(--accent-danger);
    }

    /* ========== Checker panels (MASS style) ========== */
    .checker-stats {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin: 1rem 0;
      flex-wrap: wrap;
    }
    .checker-stat {
      text-align: center;
    }
    .checker-stat .num {
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--text-primary);
    }
    .checker-stat .lbl {
      font-size: 0.7rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* ========== Misc ========== */
    .section-title {
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--text-primary);
      margin: 1.5rem 0 0.75rem;
      padding-bottom: 0.4rem;
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .section-title:first-child {
      margin-top: 0;
    }
    .hint {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 0.35rem;
    }
    .current-user {
      font-size: 0.85rem;
      color: var(--text-secondary);
      margin-bottom: 1rem;
    }
    .current-user strong {
      color: var(--text-primary);
    }

    @media (max-width: 768px) {
      .login-card {
        padding: 1.5rem;
      }
      .app-brand {
        font-size: 1.1rem;
      }
      .form-row {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }
      .checker-stats {
        gap: 1rem;
      }
      .tab-btn {
        font-size: 0.75rem;
        padding: 0.5rem 0.5rem;
      }
    }
  </style>
</head>
<body>

<!-- ==================== LOGIN VIEW ==================== -->
<div id="login-view" class="login-wrap" style="display:none;">
  <div class="login-card">
    <div class="login-brand">
      <h1>Mass CC Studio</h1>
      <p id="login-tagline">信用卡工具站</p>
    </div>
    <div class="login-error" id="login-error"></div>
    <form class="login-form" id="login-form" onsubmit="return false;">
      <div class="form-group">
        <label for="login-username" class="form-label">用户名</label>
        <input type="text" id="login-username" class="form-control" placeholder="admin" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label for="login-password" class="form-label">密码</label>
        <input type="password" id="login-password" class="form-control" placeholder="ChangeMe_2026!" autocomplete="current-password" required>
      </div>
      <div class="button" style="margin-top:1.25rem;">
        <button type="submit" class="btn btn-outline-success" id="login-btn">登录</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== APP VIEW ==================== -->
<div id="app-view" class="container-main" style="display:none;">
  <div class="app-header">
    <div class="app-brand">Mass CC Studio <span id="app-tagline"></span></div>
    <div class="app-actions">
      <button type="button" class="btn btn-outline-danger btn-sm" id="logout-btn">登出</button>
    </div>
  </div>

  <nav class="tabs-nav" id="tabs-nav">
    <button type="button" class="tab-btn active" data-tab="generate">生成</button>
    <button type="button" class="tab-btn" data-tab="checker">检测器</button>
    <button type="button" class="tab-btn" data-tab="validate">校验</button>
    <button type="button" class="tab-btn" data-tab="bin">BIN</button>
    <button type="button" class="tab-btn" data-tab="settings">设置</button>
  </nav>

  <!-- ====== Checker Tab ====== -->
  <div class="tab-panel" id="tab-checker">
    <div class="box-body">
      <div class="box-content">
        <label for="checker-input" class="form-label badge bg-danger">卡号列表</label>
        <textarea id="checker-input" class="form-control" rows="8"
          placeholder="4111111111111111|12|2026|123&#10;5301272453912345|05|25|653&#10;378282246310005|08|27|1234"
          aria-describedby="checker-format-help"></textarea>
        <small id="checker-format-help" class="format-help">格式：卡号|MM|YY|CVV（每行一条）</small>

        <div class="checker-mode-wrap">
          <span class="mode-label">检测模式</span>
          <div class="segmented" id="checker-mode-seg" role="group" aria-label="检测模式">
            <button type="button" class="seg-btn active" data-mode="normal">一般模式</button>
            <button type="button" class="seg-btn" data-mode="fast">极速模式</button>
          </div>
        </div>

        <div class="progress-bar-container" id="checker-progress-wrap" style="display:none;">
          <div class="progress-bar" id="checker-progress-bar"></div>
        </div>

        <div class="checker-stats" id="checker-stats" style="display:none;">
          <div class="checker-stat">
            <div class="num" id="checker-stat-total">0</div>
            <div class="lbl">总计</div>
          </div>
          <div class="checker-stat">
            <div class="num" id="checker-stat-processed">0</div>
            <div class="lbl">已处理</div>
          </div>
        </div>

        <div class="button" style="margin-top:1rem;">
          <button type="button" class="btn btn-outline-success" id="checker-start">开始</button>
          <button type="button" class="btn btn-outline-danger" id="checker-stop" disabled>停止</button>
        </div>
      </div>
    </div>

    <!-- Live -->
    <div class="box-title">
      <h3 class="panel-title alert alert-primary">
        <span class="panel-title-left">
          <span>Live</span>
          <span class="badge bg-success" id="checker-live-count">0</span>
        </span>
        <button type="button" class="copy-icon" id="checker-copy-live" title="复制全部 Live" aria-label="复制全部 Live" style="color:#10b981;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </button>
      </h3>
    </div>
    <div class="box-body">
      <div class="box-content alert alert-success">
        <div class="panel-body" id="checker-live-panel"></div>
      </div>
    </div>

    <!-- Die -->
    <div class="box-title">
      <h3 class="panel-title alert alert-primary">
        <span class="panel-title-left">
          <span>Die</span>
          <span class="badge bg-danger" id="checker-die-count">0</span>
        </span>
        <button type="button" class="copy-icon" id="checker-copy-die" title="复制全部 Die" aria-label="复制全部 Die" style="color:#ef4444;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </button>
      </h3>
    </div>
    <div class="box-body">
      <div class="box-content alert alert-danger">
        <div class="panel-body" id="checker-die-panel"></div>
      </div>
    </div>

    <!-- Unknown -->
    <div class="box-title">
      <h3 class="panel-title alert alert-primary">
        <span class="panel-title-left">
          <span>Unknown</span>
          <span class="badge bg-warning" id="checker-unknown-count">0</span>
        </span>
        <button type="button" class="copy-icon" id="checker-copy-unknown" title="复制全部 Unknown" aria-label="复制全部 Unknown" style="color:#d97706;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </button>
      </h3>
    </div>
    <div class="box-body">
      <div class="box-content alert alert-warning">
        <div class="panel-body" id="checker-unknown-panel"></div>
      </div>
    </div>
  </div>

  <!-- ====== Generate Tab ====== -->
  <div class="tab-panel active" id="tab-generate">
    <div class="box-body">
      <div class="box-content">
        <div class="form-row">
          <div class="form-group">
            <label for="gen-brand">品牌（留空=随机）</label>
            <select id="gen-brand" class="form-control">
              <option value="">随机</option>
              <option value="visa">Visa</option>
              <option value="mastercard">Mastercard</option>
              <option value="amex">American Express</option>
              <option value="discover">Discover</option>
              <option value="diners">Diners Club</option>
              <option value="jcb">JCB</option>
              <option value="unionpay">UnionPay</option>
              <option value="maestro">Maestro</option>
            </select>
          </div>
          <div class="form-group">
            <label for="gen-bin">BIN 前缀</label>
            <input type="text" id="gen-bin" class="form-control" placeholder="如 411111">
          </div>
          <div class="form-group">
            <label for="gen-count">数量</label>
            <input type="number" id="gen-count" class="form-control" value="100" min="1" max="100000">
          </div>
          <div class="form-group">
            <label for="gen-format">格式</label>
            <select id="gen-format" class="form-control">
              <option value="text">Text</option>
              <option value="csv">CSV</option>
              <option value="json">JSON</option>
              <option value="pipe" selected>Pipe</option>
            </select>
          </div>
          <div class="form-group">
            <label for="gen-year">有效期年份（留空=随机）</label>
            <input type="text" id="gen-year" class="form-control" placeholder="如 2026 或 26">
          </div>
        </div>
        <div class="checkbox-row">
          <label><input type="checkbox" id="gen-include-expiry" checked> 包含有效期</label>
          <label><input type="checkbox" id="gen-include-cvv" checked> 包含 CVV</label>
          <label><input type="checkbox" id="gen-mask-pan"> 掩码 PAN</label>
          <label><input type="checkbox" id="gen-validate" checked> 生成后校验</label>
        </div>
        <div class="button">
          <button type="button" class="btn btn-outline-success" id="gen-btn">生成</button>
        </div>
      </div>
    </div>
    <div class="box-body" id="gen-result-wrap" style="display:none;">
      <div class="box-content">
        <div class="output-wrap">
          <label class="form-label">生成结果</label>
          <textarea id="gen-output" class="form-control" rows="10" readonly></textarea>
          <div class="output-actions">
            <button type="button" class="btn btn-outline-success btn-sm" id="gen-copy">复制</button>
          </div>
          <div id="gen-stats" class="hint"></div>
        </div>
      </div>
    </div>
    <div class="box-body" id="gen-validation-wrap" style="display:none;">
      <div class="box-content">
        <div class="section-title">校验结果</div>
        <div id="gen-validation-results"></div>
      </div>
    </div>
  </div>

  <!-- ====== Validate Tab ====== -->
  <div class="tab-panel" id="tab-validate">
    <div class="box-body">
      <div class="box-content">
        <div class="section-title">单张校验</div>
        <div class="form-row">
          <div class="form-group">
            <label for="val-number">卡号</label>
            <input type="text" id="val-number" class="form-control" placeholder="4111111111111111">
          </div>
          <div class="form-group">
            <label for="val-month">有效期月 (MM)</label>
            <input type="text" id="val-month" class="form-control" placeholder="12">
          </div>
          <div class="form-group">
            <label for="val-year">有效期年 (YY 或 YYYY)</label>
            <input type="text" id="val-year" class="form-control" placeholder="2026">
          </div>
          <div class="form-group">
            <label for="val-cvv">CVV</label>
            <input type="text" id="val-cvv" class="form-control" placeholder="123">
          </div>
        </div>
        <div class="button">
          <button type="button" class="btn btn-outline-success" id="val-single-btn">校验</button>
        </div>
        <div id="val-single-result"></div>
      </div>
    </div>

    <div class="box-body">
      <div class="box-content">
        <div class="section-title">批量校验</div>
        <label for="val-bulk-input" class="form-label">卡号列表（每行一个）</label>
        <textarea id="val-bulk-input" class="form-control" rows="6"
          placeholder="4111111111111111&#10;5301272453912345&#10;378282246310005"></textarea>
        <div class="checkbox-row" style="margin-top:0.75rem;">
          <label><input type="checkbox" id="val-bulk-mask" checked> 掩码 PAN</label>
        </div>
        <div class="button" style="margin-top:0.75rem;">
          <button type="button" class="btn btn-outline-success" id="val-bulk-btn">批量校验</button>
        </div>
        <div id="val-bulk-result"></div>
      </div>
    </div>
  </div>

  <!-- ====== BIN Tab ====== -->
  <div class="tab-panel" id="tab-bin">
    <div class="box-body">
      <div class="box-content">
        <div class="form-row" style="grid-template-columns: 1fr auto; align-items: end;">
          <div class="form-group" style="margin-bottom:0;">
            <label for="bin-input">BIN / IIN（至少 6 位）</label>
            <input type="text" id="bin-input" class="form-control" placeholder="如 411111">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <button type="button" class="btn btn-outline-success" id="bin-btn">查询</button>
          </div>
        </div>
      </div>
    </div>
    <div class="box-body" id="bin-result-wrap" style="display:none;">
      <div class="box-content">
        <table class="result-table">
          <tbody id="bin-result-body"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ====== Settings Tab ====== -->
  <div class="tab-panel" id="tab-settings">
    <div class="box-body">
      <div class="box-content">
        <div class="current-user">当前用户：<strong id="settings-username">-</strong></div>
        <div class="form-row">
          <div class="form-group">
            <label for="set-username">新用户名（留空保持不变）</label>
            <input type="text" id="set-username" class="form-control" placeholder="admin">
          </div>
          <div class="form-group">
            <label for="set-current-password">当前密码</label>
            <input type="password" id="set-current-password" class="form-control" placeholder="必填">
          </div>
          <div class="form-group">
            <label for="set-new-password">新密码（留空保持不变）</label>
            <input type="password" id="set-new-password" class="form-control" placeholder="8-128 位">
          </div>
        </div>
        <div class="button">
          <button type="button" class="btn btn-outline-success" id="settings-save">保存</button>
        </div>
        <div id="settings-result"></div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  // ==================== API helpers ====================
  const API_BASE = 'api.php';

  async function api(action, method, body) {
    const url = API_BASE + '?action=' + encodeURIComponent(action);
    const opts = {
      method: method || 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin'
    };
    if (body && opts.method !== 'GET') {
      opts.body = JSON.stringify(body);
    }
    const res = await fetch(url, opts);
    const data = await res.json().catch(() => ({}));
    if (res.status === 401) {
      if (action === 'login') {
        throw new Error(data.error === 'Invalid credentials' ? '用户名或密码错误' : (data.error || '登录失败'));
      }
      await logout(true);
      throw new Error('会话已过期，请重新登录');
    }
    if (res.status === 429) {
      throw new Error(data.error || '请求过于频繁，请稍后再试');
    }
    if (!res.ok) {
      throw new Error(data.error || '请求失败 (' + res.status + ')');
    }
    return data;
  }

  async function logout(silent) {
    try { await api('logout', 'POST', {}); } catch (e) {}
    showLogin();
    if (!silent) {
      showLoginError('已登出');
    }
  }

  // ==================== View switching ====================
  const loginView = document.getElementById('login-view');
  const appView = document.getElementById('app-view');

  function showLogin() {
    appView.style.display = 'none';
    loginView.style.display = '';
    document.getElementById('login-password').value = '';
    document.getElementById('login-username').value = '';
  }
  function showApp() {
    loginView.style.display = 'none';
    appView.style.display = '';
  }

  // ==================== Login ====================
  const loginForm = document.getElementById('login-form');
  const loginBtn = document.getElementById('login-btn');
  const loginError = document.getElementById('login-error');

  function showLoginError(msg) {
    loginError.textContent = msg;
    loginError.classList.add('visible');
  }
  function hideLoginError() {
    loginError.classList.remove('visible');
    loginError.textContent = '';
  }

  loginForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideLoginError();
    const username = document.getElementById('login-username').value.trim();
    const password = document.getElementById('login-password').value;
    if (!username || !password) {
      showLoginError('请输入用户名和密码');
      return;
    }
    setBusy(loginBtn, true);
    try {
      await api('login', 'POST', { username, password });
      hideLoginError();
      initApp();
    } catch (err) {
      showLoginError(err.message || '登录失败');
    } finally {
      setBusy(loginBtn, false, '登录');
    }
  });

  document.getElementById('logout-btn').addEventListener('click', () => logout(false));

  // ==================== Meta / Session init ====================
  async function loadMeta() {
    try {
      const meta = await api('meta', 'GET');
      const taglineEl = document.getElementById('login-tagline');
      const appTagline = document.getElementById('app-tagline');
      if (meta.tagline) {
        if (taglineEl) taglineEl.textContent = meta.tagline;
        if (appTagline) appTagline.textContent = meta.tagline;
      }
    } catch (e) {}
  }

  async function checkSession() {
    try {
      const s = await api('session', 'GET');
      if (s.authenticated) {
        initApp();
      } else {
        showLogin();
      }
    } catch (e) {
      showLogin();
    }
  }

  // ==================== Tabs ====================
  const tabButtons = document.querySelectorAll('.tab-btn');
  const tabPanels = document.querySelectorAll('.tab-panel');

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      tabButtons.forEach(b => b.classList.toggle('active', b.dataset.tab === target));
      tabPanels.forEach(p => p.classList.toggle('active', p.id === 'tab-' + target));
    });
  });

  // ==================== Busy helper ====================
  function setBusy(el, busy, text) {
    el.disabled = busy;
    if (text !== undefined) el.textContent = busy ? (text + '中…') : text;
  }

  // ==================== Copy helper ====================
  async function writeClipboard(text) {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
      }
      return true;
    } catch (e) {
      return false;
    }
  }

  async function copyText(text, btnEl, doneText) {
    const ok = await writeClipboard(text);
    if (ok && btnEl) {
      const old = btnEl.textContent;
      btnEl.textContent = doneText || '已复制';
      setTimeout(() => { btnEl.textContent = old; }, 1200);
    }
    return ok;
  }

  // ==================== Checker Tab ====================
  const checkerInput = document.getElementById('checker-input');
  const checkerStart = document.getElementById('checker-start');
  const checkerStop = document.getElementById('checker-stop');
  const checkerProgressWrap = document.getElementById('checker-progress-wrap');
  const checkerProgressBar = document.getElementById('checker-progress-bar');
  const checkerStats = document.getElementById('checker-stats');
  const checkerStatTotal = document.getElementById('checker-stat-total');
  const checkerStatProcessed = document.getElementById('checker-stat-processed');
  const checkerLiveCount = document.getElementById('checker-live-count');
  const checkerDieCount = document.getElementById('checker-die-count');
  const checkerUnknownCount = document.getElementById('checker-unknown-count');
  const checkerLivePanel = document.getElementById('checker-live-panel');
  const checkerDiePanel = document.getElementById('checker-die-panel');
  const checkerUnknownPanel = document.getElementById('checker-unknown-panel');

  let checkerState = { running: false, stop: false, total: 0, processed: 0 };
  const checkerModeSeg = document.getElementById('checker-mode-seg');
  const checkerModeButtons = checkerModeSeg ? checkerModeSeg.querySelectorAll('.seg-btn') : [];
  let checkerMode = 'normal';

  checkerModeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      checkerMode = btn.dataset.mode;
      checkerModeButtons.forEach(b => b.classList.toggle('active', b === btn));
    });
  });

  function resetChecker() {
    checkerLiveCount.textContent = '0';
    checkerDieCount.textContent = '0';
    checkerUnknownCount.textContent = '0';
    checkerLivePanel.innerHTML = '';
    checkerDiePanel.innerHTML = '';
    checkerUnknownPanel.innerHTML = '';
    checkerStatTotal.textContent = '0';
    checkerStatProcessed.textContent = '0';
    checkerProgressBar.style.width = '0%';
  }

  function checkerIncCounter(el) {
    el.textContent = String(parseInt(el.textContent, 10) + 1);
  }

  function checkerAppend(panel, html) {
    const div = document.createElement('div');
    div.innerHTML = html;
    panel.insertBefore(div.firstElementChild || div, panel.firstChild);
  }

  function checkerApplyResult(data, res) {
    if (res.error === 1) {
      checkerAppend(checkerLivePanel, res.msg || '');
      checkerIncCounter(checkerLiveCount);
    } else if (res.error === 2) {
      checkerAppend(checkerDiePanel, res.msg || '');
      checkerIncCounter(checkerDieCount);
    } else if (res.error === 3) {
      checkerAppend(checkerUnknownPanel, res.msg || '');
      checkerIncCounter(checkerUnknownCount);
    } else {
      // format/no-data error for this line — treat as die
      const safe = String(data).replace(/"/g, '&quot;');
      const html = '<div class="result-item" data-card-data="' + safe + '"><b style="color:#ef4444;">Die</b> | ' + escapeHtml(data) + ' | ' + escapeHtml(res.message || 'Invalid') + '</div>';
      checkerAppend(checkerDiePanel, html);
      checkerIncCounter(checkerDieCount);
    }
  }

  async function checkerProcessLine(line) {
    const data = line.trim();
    if (!data) return;
    try {
      const res = await api('check', 'POST', { data });
      checkerApplyResult(data, res);
    } catch (err) {
      const safe = String(data).replace(/"/g, '&quot;');
      const html = '<div class="result-item" data-card-data="' + safe + '"><b style="color:#ef4444;">Die</b> | ' + escapeHtml(data) + ' | ' + escapeHtml(err.message || 'Error') + '</div>';
      checkerAppend(checkerDiePanel, html);
      checkerIncCounter(checkerDieCount);
    }
  }

  checkerStart.addEventListener('click', async function() {
    const lines = checkerInput.value.split('\n').map(s => s.trim()).filter(s => s !== '');
    if (lines.length === 0) {
      alert('请至少输入一条卡号数据');
      return;
    }
    resetChecker();
    checkerState = { running: true, stop: false, total: lines.length, processed: 0 };
    checkerStatTotal.textContent = String(lines.length);
    checkerStats.style.display = '';
    checkerProgressWrap.style.display = '';
    setBusy(checkerStart, true, '开始');
    checkerInput.disabled = true;

    if (checkerMode === 'fast') {
      checkerStop.disabled = true;
      checkerProgressBar.style.width = '0%';
      try {
        const res = await api('check_bulk', 'POST', { lines });
        const results = Array.isArray(res.results) ? res.results : [];
        for (let i = 0; i < lines.length; i++) {
          checkerApplyResult(lines[i], results[i] || { error: 4, message: 'No result', msg: 'No result' });
          checkerState.processed++;
          checkerStatProcessed.textContent = String(checkerState.processed);
        }
        checkerProgressBar.style.width = '100%';
      } catch (err) {
        alert(err.message || '极速检测失败');
      } finally {
        checkerState.running = false;
        setBusy(checkerStart, false, '开始');
        checkerStop.disabled = true;
        checkerInput.disabled = false;
      }
      return;
    }

    checkerStop.disabled = false;
    for (let i = 0; i < lines.length; i++) {
      if (checkerState.stop) break;
      await checkerProcessLine(lines[i]);
      checkerState.processed++;
      checkerStatProcessed.textContent = String(checkerState.processed);
      checkerProgressBar.style.width = String(Math.round((checkerState.processed / checkerState.total) * 100)) + '%';
      // small delay to avoid rate limit
      if (i < lines.length - 1) await new Promise(r => setTimeout(r, 120));
    }

    checkerState.running = false;
    setBusy(checkerStart, false, '开始');
    checkerStop.disabled = true;
    checkerInput.disabled = false;
  });

  checkerStop.addEventListener('click', function() {
    checkerState.stop = true;
    checkerStop.disabled = true;
  });

  function extractPanelData(panelId) {
    return Array.from(document.querySelectorAll('#' + panelId + ' .result-item'))
      .map(el => el.dataset.cardData || '')
      .filter(s => s !== '');
  }

  document.getElementById('checker-copy-live').addEventListener('click', async function() {
    const cards = extractPanelData('checker-live-panel');
    if (!cards.length) return;
    await copyText(cards.join('\n'), this, '已复制');
  });
  document.getElementById('checker-copy-die').addEventListener('click', async function() {
    const cards = extractPanelData('checker-die-panel');
    if (!cards.length) return;
    await copyText(cards.join('\n'), this, '已复制');
  });
  document.getElementById('checker-copy-unknown').addEventListener('click', async function() {
    const cards = extractPanelData('checker-unknown-panel');
    if (!cards.length) return;
    await copyText(cards.join('\n'), this, '已复制');
  });

  // ==================== Generate Tab ====================
  const genBtn = document.getElementById('gen-btn');
  const genResultWrap = document.getElementById('gen-result-wrap');
  const genOutput = document.getElementById('gen-output');
  const genStats = document.getElementById('gen-stats');
  const genValidationWrap = document.getElementById('gen-validation-wrap');
  const genValidationResults = document.getElementById('gen-validation-results');
  const genValidationCopyByGroup = { live: [], die: [], unknown: [] };

  genBtn.addEventListener('click', async function() {
    const count = parseInt(document.getElementById('gen-count').value, 10) || 1;
    const body = {
      count: Math.max(1, Math.min(100000, count)),
      includeExpiry: document.getElementById('gen-include-expiry').checked,
      includeCvv: document.getElementById('gen-include-cvv').checked,
      maskPan: document.getElementById('gen-mask-pan').checked,
      validate: document.getElementById('gen-validate').checked,
      format: document.getElementById('gen-format').value,
      brand: document.getElementById('gen-brand').value,
      bin: document.getElementById('gen-bin').value,
      expiryYear: document.getElementById('gen-year').value
    };
    setBusy(genBtn, true, '生成');
    try {
      const res = await api('generate', 'POST', body);
      genOutput.value = res.text || '';
      genResultWrap.style.display = '';
      genStats.textContent = '请求数量: ' + (res.count || 0) + ' | 实际生成: ' + (res.generated || 0) + ' | 已校验: ' + (res.validated || 0) + ' | 格式: ' + (res.format || 'text') + ' | 掩码: ' + (res.maskPan ? '是' : '否') + ' | 校验: ' + (res.validate ? '是' : '否');
      if (res.validate) {
        renderGenValidation(res.cards, res.maskPan);
        genValidationWrap.style.display = '';
      } else {
        genValidationWrap.style.display = 'none';
      }
    } catch (err) {
      alert(err.message || '生成失败');
    } finally {
      setBusy(genBtn, false, '生成');
    }
  });

  document.getElementById('gen-copy').addEventListener('click', async function() {
    const text = genOutput.value;
    if (!text) return;
    await copyText(text, this, '已复制');
  });

  function expiryParts(expiry) {
    const parts = expiry ? expiry.split('/') : ['', ''];
    const mm = parts[0] || '';
    const yy = parts[1] || '';
    const yyyy = yy.length === 2 ? '20' + yy : yy;
    return {
      mm: mm,
      yyyy: yyyy,
      display: mm && yyyy ? (mm + '/' + yyyy) : (expiry || '-')
    };
  }

  function renderGenValidation(cards, maskPan) {
    const buckets = { live: [], unknown: [], die: [] };
    (cards || []).forEach(function(c) {
      const v = c.validation || {};
      const s = (v.status === 'live' || v.status === 'die') ? v.status : 'unknown';
      buckets[s].push({
        number: maskPan ? (c.masked || c.number || '-') : (c.number || c.masked || '-'),
        full: c.number || c.masked || '-',
        expiry: c.expiry || '',
        cvv: c.cvv || '',
        network: v.network || c.brand || '-',
        score: (v.score === undefined || v.score === null) ? '-' : v.score,
        reason: v.reason || ''
      });
    });

    genValidationCopyByGroup.live = [];
    genValidationCopyByGroup.die = [];
    genValidationCopyByGroup.unknown = [];

    const groups = [
      { key: 'live', label: 'Live', color: '#10b981' },
      { key: 'unknown', label: 'Unknown', color: '#f59e0b' },
      { key: 'die', label: 'Die', color: '#ef4444' }
    ];
    let html = '';
    groups.forEach(function(g) {
      const items = buckets[g.key];
      if (!items.length) return;
      html += '<div class="section-title" style="display:flex;justify-content:space-between;align-items:center;color:' + g.color + ';">'
        + '<span>' + g.label + ' (' + items.length + ')</span>'
        + '<button type="button" class="copy-icon gen-copy-group" data-group="' + g.key + '" title="复制全部 ' + g.label + '" aria-label="复制全部 ' + g.label + '" style="color:' + g.color + ';">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>'
        + '</button>'
        + '</div>';
      items.forEach(function(it) {
        const ep = expiryParts(it.expiry);
        genValidationCopyByGroup[g.key].push(it.full + '|' + ep.mm + '|' + ep.yyyy + '|' + (it.cvv || ''));
        html += '<div style="padding:0.4rem 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:0.85rem;">'
          + '<b style="color:' + g.color + ';">' + g.label + '</b>'
          + ' <span style="opacity:0.7;font-size:11px;">(' + escapeHtml(it.network) + ')</span>'
          + ' | ' + escapeHtml(it.number)
          + ' | ' + escapeHtml(ep.display)
          + ' | ' + escapeHtml(it.cvv || '-')
          + ' | score <b>' + escapeHtml(String(it.score)) + '</b>'
          + ' <span style="opacity:0.7;">' + escapeHtml(it.reason) + '</span>'
          + '</div>';
      });
    });
    genValidationResults.innerHTML = html || '<div class="result-empty">无校验结果</div>';
  }

  genValidationResults.addEventListener('click', async function(e) {
    const btn = e.target.closest('.gen-copy-group');
    if (!btn) return;
    const group = btn.dataset.group;
    const lines = genValidationCopyByGroup[group] || [];
    if (!lines.length) return;
    if (!(await writeClipboard(lines.join('\n')))) return;
    btn.classList.add('copied');
    const oldTitle = btn.getAttribute('title') || '';
    btn.setAttribute('title', '已复制');
    setTimeout(() => {
      btn.classList.remove('copied');
      btn.setAttribute('title', oldTitle);
    }, 1200);
  });

  // ==================== Validate Tab ====================
  const valSingleBtn = document.getElementById('val-single-btn');
  const valSingleResult = document.getElementById('val-single-result');

  valSingleBtn.addEventListener('click', async function() {
    const body = {
      number: document.getElementById('val-number').value.trim(),
      expiryMonth: document.getElementById('val-month').value.trim() || null,
      expiryYear: document.getElementById('val-year').value.trim() || null,
      cvv: document.getElementById('val-cvv').value.trim() || null
    };
    setBusy(valSingleBtn, true, '校验');
    try {
      const res = await api('validate', 'POST', body);
      valSingleResult.innerHTML = renderValidateResult(res);
    } catch (err) {
      valSingleResult.innerHTML = '<div class="result-message error">' + escapeHtml(err.message) + '</div>';
    } finally {
      setBusy(valSingleBtn, false, '校验');
    }
  });

  function renderValidateResult(r) {
    const num = r.number || {};
    const exp = r.expiry || {};
    const cvv = r.cvv || {};
    let html = '<div class="validate-grid">';
    html += '<div class="validate-card"><h4>卡号</h4><div class="value">' + escapeHtml(num.masked || num.input || '-') + '</div></div>';
    html += '<div class="validate-card"><h4>品牌</h4><div class="value">' + escapeHtml((num.brand && num.brand.name) ? num.brand.name : 'Unknown') + '</div></div>';
    html += '<div class="validate-card"><h4>长度</h4><div class="value">' + (num.length || '-') + ' 位' + (num.lengthOk ? ' <span class="status-ok">OK</span>' : ' <span class="status-fail">Fail</span>') + '</div></div>';
    html += '<div class="validate-card"><h4>Luhn</h4><div class="value">' + (num.valid ? '<span class="status-ok">通过</span>' : '<span class="status-fail">失败</span>') + '</div></div>';
    if (exp && exp.provided) {
      html += '<div class="validate-card"><h4>有效期</h4><div class="value">' + escapeHtml(exp.message || '-') + (exp.valid ? ' <span class="status-ok">OK</span>' : ' <span class="status-fail">Fail</span>') + '</div></div>';
    }
    if (cvv && cvv.provided) {
      html += '<div class="validate-card"><h4>CVV</h4><div class="value">' + escapeHtml(cvv.message || '-') + (cvv.valid ? ' <span class="status-ok">OK</span>' : ' <span class="status-fail">Fail</span>') + '</div></div>';
    }
    html += '</div>';
    return html;
  }

  const valBulkBtn = document.getElementById('val-bulk-btn');
  const valBulkResult = document.getElementById('val-bulk-result');

  valBulkBtn.addEventListener('click', async function() {
    const raw = document.getElementById('val-bulk-input').value;
    const numbers = raw.split('\n').map(s => s.trim()).filter(s => s !== '');
    if (numbers.length === 0) {
      valBulkResult.innerHTML = '<div class="result-message error">请输入至少一个卡号</div>';
      return;
    }
    const body = {
      maskPan: document.getElementById('val-bulk-mask').checked,
      numbers: numbers
    };
    setBusy(valBulkBtn, true, '批量校验');
    try {
      const res = await api('bulk', 'POST', body);
      const results = res.results || [];
      if (results.length === 0) {
        valBulkResult.innerHTML = '<div class="result-message info">没有通过校验的卡号</div>';
      } else {
        let html = '<div class="result-message success">通过 ' + results.length + ' 条</div>';
        html += '<textarea class="form-control" rows="6" readonly>' + escapeHtml(results.map(r => r.line || r.number || '').join('\n')) + '</textarea>';
        valBulkResult.innerHTML = html;
      }
    } catch (err) {
      valBulkResult.innerHTML = '<div class="result-message error">' + escapeHtml(err.message) + '</div>';
    } finally {
      setBusy(valBulkBtn, false, '批量校验');
    }
  });

  // ==================== BIN Tab ====================
  const binBtn = document.getElementById('bin-btn');
  const binResultWrap = document.getElementById('bin-result-wrap');
  const binResultBody = document.getElementById('bin-result-body');

  binBtn.addEventListener('click', async function() {
    const bin = document.getElementById('bin-input').value.trim();
    if (!bin) {
      alert('请输入 BIN');
      return;
    }
    setBusy(binBtn, true, '查询');
    try {
      const res = await api('bin', 'POST', { bin });
      binResultWrap.style.display = '';
      binResultBody.innerHTML =
        '<tr><th>BIN</th><td>' + escapeHtml(res.bin || '-') + '</td></tr>' +
        '<tr><th>品牌</th><td>' + escapeHtml(res.brand || '-') + '</td></tr>' +
        '<tr><th>类型</th><td>' + escapeHtml(res.type || '-') + '</td></tr>' +
        '<tr><th>发卡行</th><td>' + escapeHtml(res.issuer || '-') + '</td></tr>' +
        '<tr><th>国家</th><td>' + escapeHtml(res.country || '-') + '</td></tr>' +
        '<tr><th>来源</th><td>' + escapeHtml(res.source || '-') + '</td></tr>';
    } catch (err) {
      binResultWrap.style.display = '';
      binResultBody.innerHTML = '<tr><td colspan="2" class="result-message error">' + escapeHtml(err.message) + '</td></tr>';
    } finally {
      setBusy(binBtn, false, '查询');
    }
  });

  // ==================== Settings Tab ====================
  const settingsSave = document.getElementById('settings-save');
  const settingsResult = document.getElementById('settings-result');
  const settingsUsername = document.getElementById('settings-username');

  async function loadSettings() {
    try {
      const res = await api('settings', 'GET');
      settingsUsername.textContent = res.username || '-';
    } catch (e) {}
  }

  settingsSave.addEventListener('click', async function() {
    settingsResult.innerHTML = '';
    const body = {
      currentPassword: document.getElementById('set-current-password').value,
      username: document.getElementById('set-username').value.trim(),
      newPassword: document.getElementById('set-new-password').value
    };
    if (!body.currentPassword) {
      settingsResult.innerHTML = '<div class="result-message error">当前密码不能为空</div>';
      return;
    }
    setBusy(settingsSave, true, '保存');
    try {
      const res = await api('settings', 'POST', body);
      settingsResult.innerHTML = '<div class="result-message success">保存成功</div>';
      if (res.username) {
        settingsUsername.textContent = res.username;
      }
      document.getElementById('set-current-password').value = '';
      document.getElementById('set-new-password').value = '';
      document.getElementById('set-username').value = '';
    } catch (err) {
      settingsResult.innerHTML = '<div class="result-message error">' + escapeHtml(err.message) + '</div>';
    } finally {
      setBusy(settingsSave, false, '保存');
    }
  });

  // ==================== Utils ====================
  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ==================== App init ====================
  async function initApp() {
    showApp();
    await loadSettings();
  }

  // ==================== Boot ====================
  loadMeta();
  checkSession();
})();
</script>
</body>
</html>
