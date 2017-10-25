
(function(factory) {
  if (typeof define === "function" && define.amd) {
    define(["jquery"], factory);
  } else if (typeof exports === 'object') {
    factory(require('jquery'));
  } else {
    factory(jQuery);
  }
}(function($) {
  "use strict";

/*
amazeui-markdown
reference https://github.com/toopay/bootstrapmarkdown
*/

  var Markdown = function(elementNode, options) {

    var CONFIG = [
      'autofocus',
      'savable',
      'hideable',
      'width',
      'height',
      'resize',
      'footer',
      'fullscreen',
      'hiddenButtons',
      'disabledButtons'
    ];

    // 获取定义在节点上的data属性
    $.each(CONFIG, function(i, opt) {
      if (typeof $(elementNode).data(opt) !== 'undefined') {
        options = typeof options == 'object' ? options : {};
        options[opt] = $(elementNode).data(opt);
      }
    });

    this.$name = 'am-markdown';
    this.$element = $(elementNode);
    this.$editable = {
      el: null,
      type: null,
      attrKeys: [],
      attrValues: [],
      content: null
    };
    this.$options = $.extend(true, {}, $.fn.markdown.DEFAULTS, options, this.$element.data('options'));
    this.$oldContent = null;
    this.$isPreview = false;
    this.$isFullscreen = false;
    this.$editor = null;
    this.$textarea = null;
    this.$handler = [];
    this.$callback = [];
    this.$nextTab = [];

    this.showEditor();
  };

  Markdown.prototype = {

    constructor: Markdown,

    _alterButtons: function(name, alter) {
      var handler = this.$handler,
        isAll = (name == 'all'),
        that = this;

      $.each(handler, function(k, v) {
        var halt = true;
        if (isAll) {
          halt = false;
        } else {
          halt = v.indexOf(name) < 0;
        }

        if (halt === false) {
          alter(that.$editor.find('button[data-handler="' + v + '"]'));
        }
      });
    },
    /*
    * 建立操作按钮组
     */
    _buildButtons: function(buttonsArray, container) {
      var i,
        ns = this.$name,
        handler = this.$handler,
        callback = this.$callback;

      for (i = 0; i < buttonsArray.length; i++) {

        var y, btnGroups = buttonsArray[i];
        for (y = 0; y < btnGroups.length; y++) {

          var z,
            buttons = btnGroups[y].data,
            btnGroupContainer = $('<div/>', {
              'class': 'am-btn-group'
            });

          for (z = 0; z < buttons.length; z++) {
            var button = buttons[z],
              buttonContainer, buttonIconContainer,
              buttonHandler = ns + '-' + button.name,
              buttonIcon = button.icon,

              btnText = button.btnText ? button.btnText : '',
              btnClass = button.btnClass ? button.btnClass : 'am-btn',
              tabIndex = button.tabIndex ? button.tabIndex : '-1',
              hotkey = typeof button.hotkey !== 'undefined' ? button.hotkey : '',
              hotkeyCaption = typeof jQuery.hotkeys !== 'undefined' && hotkey !== '' ? ' (' + hotkey + ')' : '';

            // button的DOM
            buttonContainer = $('<button></button>');
            buttonContainer.text(' ' + btnText).addClass('am-btn-default').addClass(btnClass);
            if (btnClass.match(/btn\-(primary|success|info|warning|danger|link)/)) {
              buttonContainer.removeClass('am-btn-default');
            }
            buttonContainer.attr({
              'type': 'button',
              'title': button.title + hotkeyCaption,
              'tabindex': tabIndex,
              'data-provider': ns,
              'data-handler': buttonHandler,
              'data-hotkey': hotkey
            });
            if (button.toggle === true) {
              buttonContainer.attr('data-toggle', 'button');
            }
            buttonIconContainer = $('<span/>');
            buttonIconContainer.addClass(buttonIcon);
            buttonIconContainer.prependTo(buttonContainer);

            btnGroupContainer.append(buttonContainer);

            handler.push(buttonHandler);
            callback.push(button.callback);
          }

          container.append(btnGroupContainer);
        }
      }

      return container;
    },
    // 事件监听：focus keyup change select
    _setListener: function() {

      var hasRows = typeof this.$textarea.attr('rows') !== 'undefined',
        maxRows = this.$textarea.val().split("\n").length > 5 ? this.$textarea.val().split("\n").length : '5',
        rowsVal = hasRows ? this.$textarea.attr('rows') : maxRows;

      // 设置高度
      this.$textarea.attr('rows', rowsVal);
      if (this.$options.resize) {
        this.$textarea.css('resize', this.$options.resize);
      }

      // 事件代理
      this.$textarea.on({
        'focus': $.proxy(this.focus, this),
        'keyup': $.proxy(this.keyup, this),
        'change': $.proxy(this.change, this),
        'select': $.proxy(this.select, this)
      });

      if (this.eventSupported('keydown')) {
        this.$textarea.on('keydown', $.proxy(this.keydown, this));
      }

      if (this.eventSupported('keypress')) {
        this.$textarea.on('keypress', $.proxy(this.keypress, this));
      }

      // Re-attach markdown data
      this.$textarea.data('markdown', this);
    },
    // 事件处理器
    _handle: function(e) {
      var target = $(e.currentTarget),
        handler = this.$handler,
        callback = this.$callback,
        handlerName = target.attr('data-handler'),
        callbackIndex = handler.indexOf(handlerName),
        callbackHandler = callback[callbackIndex];

      $(e.currentTarget).focus();

      callbackHandler(this);

      this.change(this);

      // Unless it was the save handler,
      // focusin the textarea
      if (handlerName.indexOf('cmdSave') < 0) {
        this.$textarea.focus();
      }

      e.preventDefault();
    },
    // 全屏设置
    setFullscreen: function(mode) {
      var $editor = this.$editor,
        $textarea = this.$textarea;

      if (mode === true) {
        $editor.addClass('md-fullscreen-mode');
        $('body').addClass('md-nooverflow');
        this.$options.onFullscreen(this);
      } else {
        $editor.removeClass('md-fullscreen-mode');
        $('body').removeClass('md-nooverflow');
        this.$options.onFullscreenExit(this);

        if (this.$isPreview === true)
          this.hidePreview().showPreview();
      }

      this.$isFullscreen = mode;
      $textarea.focus();
    },
    showEditor: function() {
      var instance = this,
        textarea,
        ns = this.$name,
        container = this.$element,
        originalHeigth = container.css('height'),
        originalWidth = container.css('width'),
        editable = this.$editable,
        handler = this.$handler,
        callback = this.$callback,
        options = this.$options,
        editor = $('<div/>', {
          'class': 'md-editor',
          click: function() {
            instance.focus();
          }
        });

      if (this.$editor === null) {
        var editorHeader = $('<div/>', {
          'class': 'md-header btn-toolbar'
        });

        var allBtnGroups = [];
        if (options.buttons.length > 0) allBtnGroups = allBtnGroups.concat(options.buttons[0]);
        if (options.additionalButtons.length > 0) {
          $.each(options.additionalButtons[0], function(idx, buttonGroup) {

            var matchingGroups = $.grep(allBtnGroups, function(allButtonGroup, allIdx) {
              return allButtonGroup.name === buttonGroup.name;
            });

            if (matchingGroups.length > 0) {
              matchingGroups[0].data = matchingGroups[0].data.concat(buttonGroup.data);
            } else {
              allBtnGroups.push(options.additionalButtons[0][idx]);
            }

          });
        }

        if (options.reorderButtonGroups.length > 0) {
          allBtnGroups = allBtnGroups
            .filter(function(btnGroup) {
              return options.reorderButtonGroups.indexOf(btnGroup.name) > -1;
            })
            .sort(function(a, b) {
              if (options.reorderButtonGroups.indexOf(a.name) < options.reorderButtonGroups.indexOf(b.name)) return -1;
              if (options.reorderButtonGroups.indexOf(a.name) > options.reorderButtonGroups.indexOf(b.name)) return 1;
              return 0;
            });
        }

        // 渲染按钮
        if (allBtnGroups.length > 0) {
          editorHeader = this._buildButtons([allBtnGroups], editorHeader);
        }

        if (options.fullscreen.enable) {
          editorHeader.append('<div class="md-controls"><a class="md-control md-control-fullscreen" href="#"><span class="' + options.fullscreen.icons.fullscreenOn + '"></span></a></div>').on('click', '.md-control-fullscreen', function(e) {
            e.preventDefault();
            instance.setFullscreen(true);
          });
        }

        editor.append(editorHeader);

        // 包装
        if (container.is('textarea')) {
          container.before(editor);
          textarea = container;
          textarea.addClass('md-input');
          editor.append(textarea);
        } else {
          var rawContent = (typeof toMarkdown == 'function') ? toMarkdown(container.html()) : container.html(),
            currentContent = $.trim(rawContent);

          textarea = $('<textarea/>', {
            'class': 'md-input',
            'val': currentContent
          });

          editor.append(textarea);

          editable.el = container;
          editable.type = container.prop('tagName').toLowerCase();
          editable.content = container.html();

          $(container[0].attributes).each(function() {
            editable.attrKeys.push(this.nodeName);
            editable.attrValues.push(this.nodeValue);
          });

          container.replaceWith(editor);
        }

        var editorFooter = $('<div/>', {
            'class': 'md-footer'
          }),
          createFooter = false,
          footer = '';
        // Create the footer if savable
        if (options.savable) {
          createFooter = true;
          var saveHandler = 'cmdSave';

          // Register handler and callback
          handler.push(saveHandler);
          callback.push(options.onSave);

          editorFooter.append('<button class="am-btn am-btn-success" data-provider="' +
            ns +
            '" data-handler="' +
            saveHandler +
            '"><i class="am-icon-white am-icon-ok"></i> ' +
            '保存' +
            '</button>');

        }

        footer = typeof options.footer === 'function' ? options.footer(this) : options.footer;

        if ($.trim(footer) !== '') {
          createFooter = true;
          editorFooter.append(footer);
        }

        if (createFooter) editor.append(editorFooter);

        // Set width
        if (options.width && options.width !== 'inherit') {
          if (jQuery.isNumeric(options.width)) {
            editor.css('display', 'table');
            textarea.css('width', options.width + 'px');
          } else {
            editor.addClass(options.width);
          }
        }

        // Set height
        if (options.height && options.height !== 'inherit') {
          if (jQuery.isNumeric(options.height)) {
            var height = options.height;
            if (editorHeader) height = Math.max(0, height - editorHeader.outerHeight());
            if (editorFooter) height = Math.max(0, height - editorFooter.outerHeight());
            textarea.css('height', height + 'px');
          } else {
            editor.addClass(options.height);
          }
        }

        // Reference
        this.$editor = editor;
        this.$textarea = textarea;
        this.$editable = editable;
        this.$oldContent = this.getContent();

        this._setListener();

        // Set editor attributes, data short-hand API and listener
        // 设置编辑器属性、api、监听
        this.$editor.attr('id', (new Date()).getTime());
        this.$editor.on('click', '[data-provider="am-markdown"]', $.proxy(this._handle, this));

        if (this.$element.is(':disabled') || this.$element.is('[readonly]')) {
          this.$editor.addClass('md-editor-disabled');
          this.disableButtons('all');
        }

        if (this.eventSupported('keydown') && typeof jQuery.hotkeys === 'object') {
          editorHeader.find('[data-provider="am-markdown"]').each(function() {
            var $button = $(this),
              hotkey = $button.attr('data-hotkey');
            if (hotkey.toLowerCase() !== '') {
              textarea.bind('keydown', hotkey, function() {
                $button.trigger('click');
                return false;
              });
            }
          });
        }

        if (options.initialstate === 'preview') {
          this.showPreview();
        } else if (options.initialstate === 'fullscreen' && options.fullscreen.enable) {
          this.setFullscreen(true);
        }

      } else {
        this.$editor.show();
      }

      if (options.autofocus) {
        this.$textarea.focus();
        this.$editor.addClass('active');
      }

      if (options.fullscreen.enable && options.fullscreen !== false) {
        this.$editor.append('<div class="md-fullscreen-controls">' +
          '<a href="#" class="exit-fullscreen" title="Exit fullscreen"><span class="' + options.fullscreen.icons.fullscreenOff + '">' +
          '</span></a>' +
          '</div>');
        this.$editor.on('click', '.exit-fullscreen', function(e) {
          e.preventDefault();
          instance.setFullscreen(false);
        });
      }

      // 隐藏按钮
      this.hideButtons(options.hiddenButtons);

      // 禁用按钮
      this.disableButtons(options.disabledButtons);

      // 文件拖动上传设置
      if (options.dropZoneOptions) {
        if (this.$editor.dropzone) {
          if(!options.dropZoneOptions.init) {
            options.dropZoneOptions.init = function() {
              var caretPos = 0;
              this.on('drop', function(e) {
                  caretPos = textarea.prop('selectionStart');
                  });
              this.on('success', function(file, path) {
                  var text = textarea.val();
                  textarea.val(text.substring(0, caretPos) + '\n![description](' + path + ')\n' + text.substring(caretPos));
                  });
              this.on('error', function(file, error, xhr) {
                  console.log('Error:', error);
                  });
            };
          }
          this.$textarea.addClass('dropzone');
          this.$editor.dropzone(options.dropZoneOptions);
        } else {
          console.log('dropZoneOptions was configured, but DropZone was not detected.');
        }
      }

      // 通过拖拉设置urls
      if (options.enableDropDataUri === true) {
        this.$editor.on('drop', function(e) {
          var caretPos = textarea.prop('selectionStart');
          e.stopPropagation();
          e.preventDefault();
          $.each(e.originalEvent.dataTransfer.files, function(index, file){
            var fileReader = new FileReader();
              fileReader.onload = (function(file) {
                 return function(e) {
                    var text = textarea.val();
                    textarea.val(text.substring(0, caretPos) + '\n<img src="'+ e.target.result  +'" />\n' + text.substring(caretPos) );
                 };
              })(file);
            fileReader.readAsDataURL(file);
          });
        });
      }

      // 触发onShow勾子
      options.onShow(this);

      return this;
    },
    parseContent: function(val) {
      var content;

      // markdown数据解析
      val = val || this.$textarea.val();

      if (this.$options.parser) {
        content = this.$options.parser(val);
      } else if (typeof markdown == 'object') {
        content = markdown.toHTML(val);
      } else if (typeof marked == 'function') {
        content = marked(val);
      } else {
        content = val;
      }

      return content;
    },
    // 预览
    showPreview: function() {
      var options = this.$options,
        container = this.$textarea,
        afterContainer = container.next(),
        replacementContainer = $('<div/>', {
          'class': 'md-preview',
          'data-provider': 'markdown-preview'
        }),
        content,
        callbackContent;

      if (this.$isPreview === true) {
        return this;
      }

      this.$isPreview = true;
      // 禁用其余的按钮
      this.disableButtons('all').enableButtons('cmdPreview');

      callbackContent = options.onPreview(this);
      content = typeof callbackContent == 'string' ? callbackContent : this.parseContent();

      replacementContainer.html(content);

      if (afterContainer && afterContainer.attr('class') == 'md-footer') {
        replacementContainer.insertBefore(afterContainer);
      } else {
        container.parent().append(replacementContainer);
      }

      // 预览区尺寸
      replacementContainer.css({
        width: container.outerWidth() + 'px',
        height: container.outerHeight() + 'px'
      });

      if (this.$options.resize) {
        replacementContainer.css('resize', this.$options.resize);
      }

      container.hide();

      replacementContainer.data('markdown', this);

      if (this.$element.is(':disabled') || this.$element.is('[readonly]')) {
        this.$editor.addClass('md-editor-disabled');
        this.disableButtons('all');
      }

      return this;
    },
    // 不预览
    hidePreview: function() {
      this.$isPreview = false;

      var container = this.$editor.find('div[data-provider="markdown-preview"]');

      container.remove();

      this.enableButtons('all');
      this.disableButtons(this.$options.disabledButtons);

      this.$textarea.show();
      this._setListener();

      return this;
    },
    // 检查内容是否变化
    isDirty: function() {
      return this.$oldContent != this.getContent();
    },
    getContent: function() {
      return this.$textarea.val();
    },
    setContent: function(content) {
      this.$textarea.val(content);

      return this;
    },
    findSelection: function(chunk) {
      var content = this.getContent(),
        startChunkPosition;

      if (startChunkPosition = content.indexOf(chunk), startChunkPosition >= 0 && chunk.length > 0) {
        var oldSelection = this.getSelection(),
          selection;

        this.setSelection(startChunkPosition, startChunkPosition + chunk.length);
        selection = this.getSelection();

        this.setSelection(oldSelection.start, oldSelection.end);

        return selection;
      } else {
        return null;
      }
    },
    getSelection: function() {

      var e = this.$textarea[0];

      return (

        ('selectionStart' in e && function() {
          var l = e.selectionEnd - e.selectionStart;
          return {
            start: e.selectionStart,
            end: e.selectionEnd,
            length: l,
            text: e.value.substr(e.selectionStart, l)
          };
        }) ||

        function() {
          return null;
        }

      )();

    },
    setSelection: function(start, end) {

      var e = this.$textarea[0];

      return (

        ('selectionStart' in e && function() {
          e.selectionStart = start;
          e.selectionEnd = end;
          return;
        }) ||

        function() {
          return null;
        }

      )();

    },
    replaceSelection: function(text) {

      var e = this.$textarea[0];

      return (

        ('selectionStart' in e && function() {
          e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
          e.selectionStart = e.value.length;
          return this;
        }) ||

        function() {
          e.value += text;
          return jQuery(e);
        }

      )();
    },
    getNextTab: function() {
      // Shift the nextTab
      if (this.$nextTab.length === 0) {
        return null;
      } else {
        var nextTab, tab = this.$nextTab.shift();

        if (typeof tab == 'function') {
          nextTab = tab();
        } else if (typeof tab == 'object' && tab.length > 0) {
          nextTab = tab;
        }

        return nextTab;
      }
    },
    setNextTab: function(start, end) {
      if (typeof start == 'string') {
        var that = this;
        this.$nextTab.push(function() {
          return that.findSelection(start);
        });
      } else if (typeof start == 'number' && typeof end == 'number') {
        var oldSelection = this.getSelection();

        this.setSelection(start, end);
        this.$nextTab.push(this.getSelection());

        this.setSelection(oldSelection.start, oldSelection.end);
      }

      return;
    },
    _parseButtonNameParam: function(names) {
      return typeof names == 'string' ?
        names.split(' ') :
        names;

    },
    enableButtons: function(name) {
      var buttons = this._parseButtonNameParam(name),
        that = this;

      // 通过直接设置属性改变状态，以下同
      $.each(buttons, function(i, v) {
        that._alterButtons(buttons[i], function(el) {
          el.removeAttr('disabled');
        });
      });

      return this;
    },
    disableButtons: function(name) {
      var buttons = this._parseButtonNameParam(name),
        that = this;

      $.each(buttons, function(i, v) {
        that._alterButtons(buttons[i], function(el) {
          el.attr('disabled', 'disabled');
        });
      });

      return this;
    },
    hideButtons: function(name) {
      var buttons = this._parseButtonNameParam(name),
        that = this;

      $.each(buttons, function(i, v) {
        that._alterButtons(buttons[i], function(el) {
          el.addClass('hidden');
        });
      });

      return this;
    },
    showButtons: function(name) {
      var buttons = this._parseButtonNameParam(name),
        that = this;

      $.each(buttons, function(i, v) {
        that._alterButtons(buttons[i], function(el) {
          el.removeClass('hidden');
        });
      });

      return this;
    },
    // 检测事件是否杯支持
    eventSupported: function(eventName) {
      var isSupported = eventName in this.$element;
      if (!isSupported) {
        this.$element.setAttribute(eventName, 'return;');
        isSupported = typeof this.$element[eventName] === 'function';
      }
      return isSupported;
    },
    keyup: function(e) {
      var blocked = false;
      switch (e.keyCode) {
        case 40: // 向下
        case 38: // 向上
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
          break;

        case 9: // tab
          var nextTab;
          if (nextTab = this.getNextTab(), nextTab !== null) {
            var that = this;
            setTimeout(function() {
              that.setSelection(nextTab.start, nextTab.end);
            }, 500);

            blocked = true;
          } else {
            var cursor = this.getSelection();

            if (cursor.start == cursor.end && cursor.end == this.getContent().length) {
              blocked = false;
            } else {
              this.setSelection(this.getContent().length, this.getContent().length);

              blocked = true;
            }
          }

          break;

        case 13: // enter
          blocked = false;
          break;
        case 27: // escape
          if (this.$isFullscreen) this.setFullscreen(false);
          blocked = false;
          break;

        default:
          blocked = false;
      }

      if (blocked) {
        e.stopPropagation();
        e.preventDefault();
      }

      this.$options.onChange(this);
    },
    change: function(e) {
      this.$options.onChange(this);
      return this;
    },
    select: function(e) {
      this.$options.onSelect(this);
      return this;
    },
    focus: function(e) {
      var options = this.$options,
        isHideable = options.hideable,
        editor = this.$editor;

      editor.addClass('active');

      $(document).find('.md-editor').each(function() {
        if ($(this).attr('id') !== editor.attr('id')) {
          var attachedMarkdown;

          if (attachedMarkdown = $(this).find('textarea').data('markdown'),
            attachedMarkdown === null) {
            attachedMarkdown = $(this).find('div[data-provider="markdown-preview"]').data('markdown');
          }

          if (attachedMarkdown) {
            attachedMarkdown.blur();
          }
        }
      });

      // Trigger the onFocus hook
      options.onFocus(this);

      return this;
    },
    blur: function(e) {
      var options = this.$options,
        isHideable = options.hideable,
        editor = this.$editor,
        editable = this.$editable;

      if (editor.hasClass('active') || this.$element.parent().length === 0) {
        editor.removeClass('active');

        if (isHideable) {
          // Check for editable elements
          if (editable.el !== null) {
            // Build the original element
            var oldElement = $('<' + editable.type + '/>'),
              content = this.getContent(),
              currentContent = this.parseContent(content);

            $(editable.attrKeys).each(function(k, v) {
              oldElement.attr(editable.attrKeys[k], editable.attrValues[k]);
            });

            // Get the editor content
            oldElement.html(currentContent);

            editor.replaceWith(oldElement);
          } else {
            editor.hide();
          }
        }

        // Trigger the onBlur hook
        options.onBlur(this);
      }

      return this;
    },
    popover: function() {
      var pop = `<div class="am-modal am-modal-no-btn" tabindex="-1" id="your-modal">
        <div class="am-modal-dialog">
          <div class="am-modal-hd">Modal 标题
            <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
          </div>
          <div class="am-modal-bd">
            <div class="am-input-group">
              <span class="am-input-group-label"><i class="am-icon-user am-icon-fw"></i></span>
              <input type="text" class="am-form-field" placeholder="Username">
            </div>
          </div>
        </div>
      </div>`;

    }

  };

  var old = $.fn.markdown;

  $.fn.markdown = function(option) {
    return this.each(function() {
      var $this = $(this),
        data = $this.data('markdown'),
        options = typeof option == 'object' && option;
      if (!data)
        $this.data('markdown', (data = new Markdown(this, options)));
    });
  };

  $.fn.markdown.messages = {};

  /*
   * 默认配置信息
   *===================================================================*/
  $.fn.markdown.DEFAULTS = {

    autofocus: false,
    hideable: false,
    savable: false,
    width: 'inherit',
    height: 'inherit',
    resize: 'none',
    initialstate: 'editor',
    parser: null,
    dropZoneOptions: null,
    enableDropDataUri: false,

    /*
     * 按钮配置
     *=================================================================*/
    buttons: [
      [{
        name: 'groupFont',
        data: [{
          name: 'cmdBold',
          hotkey: 'Ctrl+B',
          title: '粗体',
          icon: 'am-icon-bold am-secondary',

          callback: function(e) {

            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              // 设置提示信息
              chunk = '粗体文字';
            } else {
              chunk = selected.text;
            }

            // 触发粗体与设置光标位置
            if (content.substr(selected.start - 2, 2) === '**' &&
              content.substr(selected.end, 2) === '**') {
              e.setSelection(selected.start - 2, selected.end + 2);
              e.replaceSelection(chunk);
              cursor = selected.start - 2;
            } else {
              e.replaceSelection('**' + chunk + '**');
              cursor = selected.start + 2;
            }

            // 设置鼠标位置
            e.setSelection(cursor, cursor + chunk.length);
          }
        },
        {
          name: 'cmdItalic',
          title: '斜体',
          hotkey: 'Ctrl+I',
          icon: 'am-icon-italic ',
          callback: function(e) {

            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              chunk = '斜体文字';
            } else {
              chunk = selected.text;
            }

            if (content.substr(selected.start - 1, 1) === '_' &&
              content.substr(selected.end, 1) === '_') {
              e.setSelection(selected.start - 1, selected.end + 1);
              e.replaceSelection(chunk);
              cursor = selected.start - 1;
            } else {
              e.replaceSelection('_' + chunk + '_');
              cursor = selected.start + 1;
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        },
        {
          name: 'cmdHeading',
          title: '标题',
          hotkey: 'Ctrl+H',
          icon: 'am-icon-header ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent(),
              pointer, prevChar;

            if (selected.length === 0) {
              chunk = '标题文字';
            } else {
              chunk = selected.text + '\n';
            }

            if ((pointer = 4, content.substr(selected.start - pointer, pointer) === '### ') ||
              (pointer = 3, content.substr(selected.start - pointer, pointer) === '###')) {
              e.setSelection(selected.start - pointer, selected.end);
              e.replaceSelection(chunk);
              cursor = selected.start - pointer;
            } else if (selected.start > 0 && (prevChar = content.substr(selected.start - 1, 1), !!prevChar && prevChar != '\n')) {
              e.replaceSelection('\n\n### ' + chunk);
              cursor = selected.start + 6;
            } else {
              // Empty string before element
              e.replaceSelection('### ' + chunk);
              cursor = selected.start + 4;
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        }]
      },
      {
        name: 'groupLink',
        data: [{
          name: 'cmdUrl',
          title: '链接',
          hotkey: 'Ctrl+L',
          icon: 'am-icon-link ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent(),
              link;

            if (selected.length === 0) {
              chunk = '链接描述';
            } else {
              chunk = selected.text;
            }

            link = prompt('输入链接地址', 'http://');

            var urlRegex = new RegExp('^((http|https)://|(mailto:)|(//))[a-z0-9]', 'i');
            if (link !== null && link !== '' && link !== 'http://' && urlRegex.test(link)) {
              var sanitizedLink = $('<div>' + link + '</div>').text();

              e.replaceSelection('[' + chunk + '](' + sanitizedLink + ')');
              cursor = selected.start + 1;

              e.setSelection(cursor, cursor + chunk.length);
            }
          }
        },
        {
          name: 'cmdImage',
          title: '图片',
          hotkey: 'Ctrl+G',
          icon: 'am-icon-picture-o ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent(),
              link;

            if (selected.length === 0) {
              chunk = '描述下图片吧';
            } else {
              chunk = selected.text;
            }

            link = prompt('输入图片地址', 'http://');

            var urlRegex = new RegExp('^((http|https)://|(//))[a-z0-9]', 'i');
            if (link !== null && link !== '' && link !== 'http://' && urlRegex.test(link)) {
              var sanitizedLink = $('<div>' + link + '</div>').text();

              e.replaceSelection('![' + chunk + '](' + sanitizedLink + ' "' + '输入图片标题' + '")');
              cursor = selected.start + 2;

              e.setNextTab('在这里输入图片标题');

              e.setSelection(cursor, cursor + chunk.length);
            }
          }
        }]
      }, {
        name: 'groupMisc',
        data: [{
          name: 'cmdList',
          hotkey: 'Ctrl+U',
          title: '无序列表',
          icon: 'am-icon-list-ul ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              chunk = '无序列表';

              e.replaceSelection('- ' + chunk);
              cursor = selected.start + 2;
            } else {
              if (selected.text.indexOf('\n') < 0) {
                chunk = selected.text;

                e.replaceSelection('- ' + chunk);

                cursor = selected.start + 2;
              } else {
                var list = [];

                list = selected.text.split('\n');
                chunk = list[0];

                $.each(list, function(k, v) {
                  list[k] = '- ' + v;
                });

                e.replaceSelection('\n\n' + list.join('\n'));

                cursor = selected.start + 4;
              }
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        }, {
          name: 'cmdListO',
          hotkey: 'Ctrl+O',
          title: '有序列表',
          icon: 'am-icon-list-ol ',
          callback: function(e) {

            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              chunk = '有序列表';
              e.replaceSelection('1. ' + chunk);
              cursor = selected.start + 3;
            } else {
              if (selected.text.indexOf('\n') < 0) {
                chunk = selected.text;

                e.replaceSelection('1. ' + chunk);

                cursor = selected.start + 3;
              } else {
                var i = 1;
                var list = [];

                list = selected.text.split('\n');
                chunk = list[0];

                $.each(list, function(k, v) {
                  list[k] = i + '. ' + v;
                  i++;
                });

                e.replaceSelection('\n\n' + list.join('\n'));

                cursor = selected.start + 5;
              }
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        }, {
          name: 'cmdCode',
          hotkey: 'Ctrl+K',
          title: '代码',
          icon: 'am-icon-code ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              chunk = '在这里输入代码';
            } else {
              chunk = selected.text;
            }

            if (content.substr(selected.start - 4, 4) === '```\n' &&
              content.substr(selected.end, 4) === '\n```') {
              e.setSelection(selected.start - 4, selected.end + 4);
              e.replaceSelection(chunk);
              cursor = selected.start - 4;
            } else if (content.substr(selected.start - 1, 1) === '`' &&
              content.substr(selected.end, 1) === '`') {
              e.setSelection(selected.start - 1, selected.end + 1);
              e.replaceSelection(chunk);
              cursor = selected.start - 1;
            } else if (content.indexOf('\n') > -1) {
              e.replaceSelection('```\n' + chunk + '\n```');
              cursor = selected.start + 4;
            } else {
              e.replaceSelection('`' + chunk + '`');
              cursor = selected.start + 1;
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        }, {
          name: 'cmdQuote',
          hotkey: 'Ctrl+Q',
          title: '引用',
          icon:  'am-icon-quote-left ',
          callback: function(e) {
            var chunk, cursor, selected = e.getSelection(),
              content = e.getContent();

            if (selected.length === 0) {
              chunk = '引用文字';

              e.replaceSelection('> ' + chunk);

              cursor = selected.start + 2;
            } else {
              if (selected.text.indexOf('\n') < 0) {
                chunk = selected.text;

                e.replaceSelection('> ' + chunk);

                cursor = selected.start + 2;
              } else {
                var list = [];

                list = selected.text.split('\n');
                chunk = list[0];

                $.each(list, function(k, v) {
                  list[k] = '> ' + v;
                });

                e.replaceSelection('\n\n' + list.join('\n'));

                cursor = selected.start + 4;
              }
            }

            e.setSelection(cursor, cursor + chunk.length);
          }
        }]
      }, {
        name: 'groupUtil',
        data: [{
          name: 'cmdPreview',
          toggle: true,
          hotkey: 'Ctrl+P',
          title: '预览',
          btnText: '预览',
          btnClass: 'am-btn am-btn-primary',
          icon:  'am-icon-tv ',
          callback: function(e) {
            var isPreview = e.$isPreview,
              content;

            if (isPreview === false) {
              e.showPreview();
            } else {
              e.hidePreview();
            }
          }
        }]
      }]
    ],
    additionalButtons: [], // 添加额外的按钮
    reorderButtonGroups: [],
    hiddenButtons: [], // 隐藏默认的按钮
    disabledButtons: [], // 默认禁止的按钮
    footer: '',
    fullscreen: {
      enable: true,
      icons: {
        fullscreenOn:  'am-icon-arrows-alt ',
        fullscreenOff:  'am-icon-compress ',
      }
    },

    /* 事件勾子 */
    onShow: function(e) {},
    onPreview: function(e) {},
    onSave: function(e) {},
    onBlur: function(e) {},
    onFocus: function(e) {},
    onChange: function(e) {},
    onFullscreen: function(e) {},
    onFullscreenExit: function(e) {},
    onSelect: function(e) {}
  };

  $.fn.markdown.Constructor = Markdown;


  $.fn.markdown.noConflict = function() {
    $.fn.markdown = old;
    return this;
  };

  /* 初始化
   * ==================================== */
  var initMarkdown = function(el) {
    var $this = el;

    if ($this.data('markdown')) {
      $this.data('markdown').showEditor();
      return;
    }

    $this.markdown();
  };

  var blurNonFocused = function(e) {
    var $activeElement = $(document.activeElement);

    $(document).find('.md-editor').each(function() {
      var $this = $(this),
        focused = $activeElement.closest('.md-editor')[0] === this,
        attachedMarkdown = $this.find('textarea').data('markdown') ||
        $this.find('div[data-provider="markdown-preview"]').data('markdown');

      if (attachedMarkdown && !focused) {
        attachedMarkdown.blur();
      }
    });
  };

  $(document)
    .on('click.markdown.data-api', '[data-provide="am-md-editable"]', function(e) {
      initMarkdown($(this));
      e.preventDefault();
    })
    .on('click focusin', function(e) {
      blurNonFocused(e);
    })
    .ready(function() {
      $('textarea[data-am-md]').each(function() {
        initMarkdown($(this));
      });
    });

}));
