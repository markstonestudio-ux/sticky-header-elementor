(function () {
  'use strict';
  var bp = (window.sheConfig && window.sheConfig.breakpoints) || { mobile: 767, tablet: 1024 };

  function getViewport() {
    var w = window.innerWidth;
    if (w <= bp.mobile) return 'mobile';
    if (w <= bp.tablet) return 'tablet';
    return 'desktop';
  }

  function StickyHeader(el) {
    this.el = el;
    this.cfg = {};
    this.active = false;
    this.hidden = false;
    this.placeholder = null;
    this.lastScrollY = window.scrollY || window.pageYOffset;
    this.ticking = false;
    this._boundScroll = this._onScroll.bind(this);
    this._boundResize = this._onResize.bind(this);
    this._parse();
    this._init();
  }

  StickyHeader.prototype._parse = function () {
    try { this.cfg = JSON.parse(this.el.getAttribute('data-she-config') || '{}'); }
    catch (e) { this.cfg = {}; }
  };

  StickyHeader.prototype._init = function () {
    this._applyBaseTransition();
    window.addEventListener('scroll', this._boundScroll, { passive: true });
    window.addEventListener('resize', this._boundResize, { passive: true });
    this._check();
  };

  StickyHeader.prototype._applyBaseTransition = function () {
    var d = this.cfg.duration || 350;
    var e = this.cfg.easing  || 'ease';
    this.el.style.transition = [
      'transform '        + d + 'ms ' + e,
      'opacity '          + d + 'ms ' + e,
      'padding '          + d + 'ms ' + e,
      'background-color ' + d + 'ms ' + e,
      'box-shadow '       + d + 'ms ' + e,
      'backdrop-filter '  + d + 'ms ' + e,
    ].join(', ');
    this.el.style.willChange = 'transform, opacity, background-color';
  };

  StickyHeader.prototype._shouldDisable = function () {
    var vp = getViewport();
    if (vp === 'mobile' && this.cfg.disableMobile) return true;
    if (vp === 'tablet' && this.cfg.disableTablet) return true;
    return false;
  };

  StickyHeader.prototype._onScroll = function () {
    if (!this.ticking) {
      this.ticking = true;
      var self = this;
      requestAnimationFrame(function () { self._check(); self.ticking = false; });
    }
  };

  StickyHeader.prototype._check = function () {
    if (this._shouldDisable()) {
      if (this.active) this._deactivate();
      this.lastScrollY = window.scrollY || window.pageYOffset;
      return;
    }
    var scrollY = window.scrollY || window.pageYOffset;
    var offset  = this.cfg.offset || 0;
    var mode    = this.cfg.mode   || 'always';
    var goingDown = scrollY > this.lastScrollY;
    var shouldStick = false;

    if (mode === 'always')        shouldStick = scrollY > offset;
    else if (mode === 'scroll-up') shouldStick = scrollY > offset && !goingDown;
    else if (mode === 'after-offset') shouldStick = scrollY > offset;

    if (shouldStick && !this.active) this._activate();
    else if (!shouldStick && this.active) this._deactivate();

    if (this.active) {
      if (mode === 'always' && this.cfg.hideOnDown) {
        goingDown && scrollY > offset ? this._slideOut() : this._slideIn();
      } else if (mode === 'scroll-up') {
        goingDown ? this._slideOut() : this._slideIn();
      }
    }
    this.lastScrollY = scrollY;
  };

  StickyHeader.prototype._activate = function () {
    if (this.active) return;
    this.active = true;
    var el = this.el, cfg = this.cfg;
    this._createPlaceholder();
    el.style.position = 'fixed';
    el.style.top      = '0';
    el.style.left     = '0';
    el.style.right    = '0';
    el.style.width    = '100%';
    el.style.zIndex   = String(cfg.zIndex || 9999);
    el.classList.add('she-sticky-active');
    if (cfg.stickyClass) el.classList.add(cfg.stickyClass);
    if (cfg.bgColor) el.style.backgroundColor = cfg.bgColor;
    if (cfg.blur > 0) {
      el.style.backdropFilter = 'blur(' + cfg.blur + 'px)';
      el.style.webkitBackdropFilter = 'blur(' + cfg.blur + 'px)';
    }
    if (cfg.shadow) {
      el.style.boxShadow = '0 4px ' + (cfg.shadowSize || 20) + 'px ' + (cfg.shadowColor || 'rgba(0,0,0,0.12)');
    }
    if (cfg.borderBottom) {
      el.style.borderBottom = '1px solid ' + (cfg.borderColor || 'rgba(0,0,0,0.08)');
    }
    if (cfg.padding) {
      if (cfg.padding.top)    el.style.paddingTop    = cfg.padding.top;
      if (cfg.padding.right)  el.style.paddingRight  = cfg.padding.right;
      if (cfg.padding.bottom) el.style.paddingBottom = cfg.padding.bottom;
      if (cfg.padding.left)   el.style.paddingLeft   = cfg.padding.left;
    }
    this._playEnterAnimation();
  };

  StickyHeader.prototype._deactivate = function () {
    if (!this.active) return;
    this.active = false;
    this.hidden = false;
    var el = this.el, cfg = this.cfg;
    el.classList.remove('she-sticky-active', 'she-hidden');
    if (cfg.stickyClass) el.classList.remove(cfg.stickyClass);
    ['position','top','left','right','width','zIndex','backgroundColor',
     'backdropFilter','webkitBackdropFilter','boxShadow','borderBottom',
     'paddingTop','paddingRight','paddingBottom','paddingLeft','transform','opacity','pointerEvents'
    ].forEach(function(p) { el.style[p] = ''; });
    this._removePlaceholder();
  };

  StickyHeader.prototype._slideOut = function () {
    if (this.hidden) return;
    this.hidden = true;
    var anim = this.cfg.animation || 'slide-down';
    this.el.classList.add('she-hidden');
    if (anim === 'fade-in' || anim === 'zoom-in') {
      this.el.style.opacity = '0';
      this.el.style.pointerEvents = 'none';
      if (anim === 'zoom-in') this.el.style.transform = 'scaleY(0.6) translateY(-50%)';
    } else {
      this.el.style.transform = 'translateY(-110%)';
    }
  };

  StickyHeader.prototype._slideIn = function () {
    if (!this.hidden) return;
    this.hidden = false;
    this.el.classList.remove('she-hidden');
    this.el.style.transform = '';
    this.el.style.opacity = '';
    this.el.style.pointerEvents = '';
  };

  StickyHeader.prototype._playEnterAnimation = function () {
    var el = this.el, anim = this.cfg.animation || 'slide-down';
    if (anim === 'none') return;
    if (anim === 'slide-down') {
      el.style.transform = 'translateY(-100%)';
      requestAnimationFrame(function () { el.style.transform = 'translateY(0)'; });
    } else if (anim === 'fade-in') {
      el.style.opacity = '0';
      requestAnimationFrame(function () { el.style.opacity = '1'; });
    } else if (anim === 'zoom-in') {
      el.style.transform = 'scaleY(0.8) translateY(-20%)';
      el.style.opacity = '0';
      requestAnimationFrame(function () {
        el.style.transform = 'scaleY(1) translateY(0)';
        el.style.opacity = '1';
      });
    }
  };

  StickyHeader.prototype._createPlaceholder = function () {
    if (this.placeholder) return;
    var rect = this.el.getBoundingClientRect();
    var ph = document.createElement('div');
    ph.className = 'she-placeholder';
    ph.style.height = rect.height + 'px';
    ph.style.width = '100%';
    ph.style.display = 'block';
    ph.style.visibility = 'hidden';
    ph.setAttribute('aria-hidden', 'true');
    this.el.parentNode.insertBefore(ph, this.el);
    this.placeholder = ph;
  };

  StickyHeader.prototype._removePlaceholder = function () {
    if (this.placeholder) {
      this.placeholder.parentNode && this.placeholder.parentNode.removeChild(this.placeholder);
      this.placeholder = null;
    }
  };

  StickyHeader.prototype._onResize = function () {
    if (this.active && this.placeholder) {
      this.placeholder.style.height = this.el.getBoundingClientRect().height + 'px';
    }
    this._check();
  };

  StickyHeader.prototype.destroy = function () {
    window.removeEventListener('scroll', this._boundScroll);
    window.removeEventListener('resize', this._boundResize);
    this._deactivate();
  };

  function initAll() {
    document.querySelectorAll('[data-she="1"]').forEach(function (el) {
      if (!el._sheInstance) el._sheInstance = new StickyHeader(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
  window.addEventListener('load', initAll);
  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function () { initAll(); });
  }
  document.addEventListener('she:reinit', initAll);
  window.SHE = {
    version: '1.0.0',
    init: initAll,
    reinit: function () {
      document.querySelectorAll('[data-she="1"]').forEach(function (el) {
        if (el._sheInstance) { el._sheInstance.destroy(); el._sheInstance = null; }
      });
      initAll();
    },
  };
})();
