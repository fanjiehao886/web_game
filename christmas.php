<?php
include_once 'conn.php';
require_once "jssdk.php";
$jssdk = new JSSDK("wx6e430c2f6cf05ae9", "496df986ec85edda2e7e5432b06e4c19");
$signPackage = $jssdk->GetSignPackage();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <title>我在圣诞树挂苹果写下了祝福</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      background: #000;
      width: 100%;
      height: 100%;
      overflow: hidden;
    }
    #game-container {
      width: 100vw;
      height: 100vh;
    }
    canvas {
      display: block;
    }
  </style>
  <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
  <script src="assets/js/phaser.min.js"></script>
</head>
<body>
  <div id="game-container"></div>

<!-- 盲盒专用二维码弹窗 -->
  <div id="blindbox-qrcode-overlay" style="
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.7);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9999;
  ">
    <div style="position:relative;width:70vw;max-width:340px;display:flex;flex-direction:column;align-items:center;">
      <button id="blindbox-qrcode-close-btn" style="
        position:absolute;
        right:-10px;
        top:-10px;
        width:30px;
        height:30px;
        border-radius:50%;
        border:none;
        background:rgba(0,0,0,0.6);
        color:#fff;
        font-size:18px;
        line-height:30px;
        text-align:center;
        cursor:pointer;
        user-select: none;
        -webkit-tap-highlight-color: transparent;">×</button>
      <div style="color:#fff;font-size:18px;margin-bottom:12px;text-align:center;white-space:pre-line;">
        长按关注，回复“礼物”开启圣诞盲盒！
      </div>
      <div style="position:relative;display:inline-block;">
        <img id="blindbox-qrcode-img" src="assets/images/caishen/qrcode.png" alt="二维码" style="width:60vw;max-width:300px;" />
      </div>
    </div>
  </div>

  <!-- 盲盒口令弹窗 -->
  <div id="password-overlay" style="
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.7);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9998;
  ">
    <div style="position:relative;width:80vw;max-width:380px;background:#1c1c1c;border-radius:8px;padding:16px;box-sizing:border-box;color:#fff;">
      <div style="font-size:18px;margin-bottom:10px;text-align:center;">输入口令，打开圣诞盲盒</div>
      <input id="password-input" type="password" placeholder="请输入口令" style="width:100%;box-sizing:border-box;border-radius:4px;border:none;padding:8px;font-size:16px;outline:none;" />
      <div style="margin-top:12px;display:flex;justify-content:space-between;">
        <button id="password-cancel" style="flex:1;margin-right:8px;height:36px;border:none;border-radius:4px;background:#555;color:#fff;font-size:16px;">取消</button>
        <button id="password-ok" style="flex:1;margin-left:8px;height:36px;border:none;border-radius:4px;background:#ff4d4f;color:#fff;font-size:16px;">确定</button>
      </div>
    </div>
  </div>

  <!-- 编辑祝福语弹窗 -->
  <div id="bless-edit-overlay" style="
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.7);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9998;
  ">
    <div style="position:relative;width:80vw;max-width:380px;background:#1c1c1c;border-radius:8px;padding:16px;box-sizing:border-box;color:#fff;">
      <div style="font-size:18px;margin-bottom:10px;text-align:center;">写下你的圣诞祝福</div>
      <textarea id="bless-edit-text" rows="4" style="width:100%;box-sizing:border-box;border-radius:4px;border:none;padding:8px;font-size:16px;resize:none;outline:none;"></textarea>
      <div style="margin-top:12px;display:flex;justify-content:space-between;">
        <button id="bless-edit-cancel" style="flex:1;margin-right:8px;height:36px;border:none;border-radius:4px;background:#555;color:#fff;font-size:16px;">取消</button>
        <button id="bless-edit-ok" style="flex:1;margin-left:8px;height:36px;border:none;border-radius:4px;background:#ff4d4f;color:#fff;font-size:16px;">挂上祝福</button>
      </div>
    </div>
  </div>

  <!-- 查看祝福语弹窗 -->
  <div id="bless-view-overlay" style="
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.7);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9998;
  ">
    <div style="position:relative;width:80vw;max-width:380px;background:#1c1c1c;border-radius:8px;padding:16px;box-sizing:border-box;color:#fff;">
      <div style="font-size:18px;margin-bottom:10px;text-align:center;">圣诞祝福</div>
      <div id="bless-view-text" style="min-height:60px;font-size:16px;line-height:1.5;white-space:pre-wrap;"></div>
      <div style="margin-top:12px;text-align:center;">
        <button id="bless-view-close" style="width:120px;height:36px;border:none;border-radius:4px;background:#ff4d4f;color:#fff;font-size:16px;">关闭</button>
      </div>
    </div>
  </div>

  <script>
    // 微信 JS-SDK 配置，用于分享（在非微信环境下安全降级）
    if (typeof wx !== 'undefined') {
      wx.config({
        debug: false,
        appId: '<?php echo $signPackage["appId"]; ?>',
        timestamp: <?php echo $signPackage["timestamp"]; ?>,
        nonceStr: '<?php echo $signPackage["nonceStr"]; ?>',
        signature: '<?php echo $signPackage["signature"]; ?>',
        jsApiList: [
          'updateAppMessageShareData',
          'updateTimelineShareData',
          'onMenuShareAppMessage',
          'onMenuShareTimeline'
        ]
      });

      wx.ready(function () {
        var shareTitle = '圣诞树挂苹果写祝福';
        var shareDesc  = '在圣诞树上挂一个苹果，留下你的圣诞祝福。';
        var shareLink  = window.location.href.split('#')[0];
        var shareImg   = window.location.origin + '/assets/images/christmas/apple.png';

        // 分享给朋友（新版接口优先，旧版作为兼容）
        if (wx.updateAppMessageShareData) {
          wx.updateAppMessageShareData({
            title: shareTitle,
            desc: shareDesc,
            link: shareLink,
            imgUrl: shareImg
          });
        } else if (wx.onMenuShareAppMessage) {
          wx.onMenuShareAppMessage({
            title: shareTitle,
            desc: shareDesc,
            link: shareLink,
            imgUrl: shareImg
          });
        }

        // 分享到朋友圈（新版接口优先，旧版作为兼容）
        if (wx.updateTimelineShareData) {
          wx.updateTimelineShareData({
            title: shareTitle,
            link: shareLink,
            imgUrl: shareImg
          });
        } else if (wx.onMenuShareTimeline) {
          wx.onMenuShareTimeline({
            title: shareTitle,
            link: shareLink,
            imgUrl: shareImg
          });
        }
      });

      wx.error(function (res) {
        console.error('wx.config 验证失败:', res);
      });
    }

    const GAME_WIDTH = 720;
    const GAME_HEIGHT = 1280;

    // 控制二维码弹窗和游戏开始的简单逻辑
    let christmasGameCanStart = false;

    // 与后台交互的简单工具函数
    async function fetchChristmasApples() {
      try {
        const resp = await fetch('christmas_api.php?action=list', {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        });
        if (!resp.ok) return [];
        const data = await resp.json();
        if (Array.isArray(data)) {
          return data;
        }
        return [];
      } catch (e) {
        console.error('加载苹果列表失败', e);
        return [];
      }
    }

    async function saveChristmasApplePosition(payload) {
      try {
        const resp = await fetch('christmas_api.php?action=save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        if (!resp.ok) return null;
        const data = await resp.json();
        if (data && data.success) {
          return data;
        }
        return null;
      } catch (e) {
        console.error('保存苹果位置失败', e);
        return null;
      }
    }

    function showChristmasQrcode() {
      const overlay = document.getElementById('qrcode-overlay');
      if (overlay) {
        overlay.style.display = 'flex';
      }
    }

    function hideChristmasQrcodeAndStartGame() {
      const overlay = document.getElementById('qrcode-overlay');
      if (overlay) {
        overlay.style.display = 'none';
      }

      // 允许进入主场景
      christmasGameCanStart = true;

      // 如果 Phaser 已经加载完成并且还没进入主场景，则启动 MainScene
      if (window.game && window.game.scene && window.game.scene.getScene('LoadingScene')) {
        const loadingScene = window.game.scene.getScene('LoadingScene');
        if (loadingScene && loadingScene.startMainSceneIfReady) {
          loadingScene.startMainSceneIfReady();
        }
      }
    }

    // 绑定关闭按钮和覆盖层事件，防止点击穿透到下层 canvas
    document.addEventListener('DOMContentLoaded', () => {
      const closeBtn = document.getElementById('qrcode-close-btn');
      if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          hideChristmasQrcodeAndStartGame();
        });
      }

      // 绑定祝福语弹窗按钮
      const blessEditOverlay = document.getElementById('bless-edit-overlay');
      const blessEditCancel = document.getElementById('bless-edit-cancel');
      const blessEditOk = document.getElementById('bless-edit-ok');
      const blessViewOverlay = document.getElementById('bless-view-overlay');
      const blessViewClose = document.getElementById('bless-view-close');

      // 盲盒二维码与口令弹窗
      const blindboxQrcodeOverlay = document.getElementById('blindbox-qrcode-overlay');
      const blindboxQrcodeCloseBtn = document.getElementById('blindbox-qrcode-close-btn');
      const passwordOverlay = document.getElementById('password-overlay');
      const passwordInput = document.getElementById('password-input');
      const passwordCancel = document.getElementById('password-cancel');
      const passwordOk = document.getElementById('password-ok');

      // 统一处理所有覆盖层的事件：在捕获阶段拦截 pointer/touch/mouse，避免穿透到 canvas
      const overlayConfigs = [
        { id: 'qrcode-overlay', innerSelector: '#qrcode-overlay > div' },
        { id: 'blindbox-qrcode-overlay', innerSelector: '#blindbox-qrcode-overlay > div' },
        { id: 'password-overlay', innerSelector: '#password-overlay > div' },
        { id: 'bless-edit-overlay', innerSelector: '#bless-edit-overlay > div' },
        { id: 'bless-view-overlay', innerSelector: '#bless-view-overlay > div' }
      ];

      overlayConfigs.forEach(({ id, innerSelector }) => {
        const overlay = document.getElementById(id);
        if (!overlay) return;

        const inner = document.querySelector(innerSelector);

        // 捕获阶段：先于 Phaser 获取事件，区分点击位置
        ['pointerdown', 'touchstart', 'mousedown'].forEach((evt) => {
          overlay.addEventListener(
            evt,
            (e) => {
              // 如果点击不在内容区域内，彻底吃掉事件，防止任何穿透
              if (!inner || !inner.contains(e.target)) {
                e.stopPropagation();
                e.preventDefault();
              } else {
                // 在内容区域内，只阻止冒泡，不阻止默认行为（保证输入/按钮正常）
                e.stopPropagation();
              }
            },
            true
          );
        });

        // 冒泡阶段 click 拦截兜底
        overlay.addEventListener('click', (e) => {
          e.stopPropagation();
        });
      });

      if (blessEditCancel && blessEditOverlay) {
        blessEditCancel.addEventListener('click', (e) => {
          e.stopPropagation();
          blessEditOverlay.style.display = 'none';
        });
      }

      if (blessEditOk) {
        blessEditOk.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (window.currentBlessEdit && typeof window.currentBlessEdit.onConfirm === 'function') {
            await window.currentBlessEdit.onConfirm();
          }
        });
      }

      if (blessViewClose && blessViewOverlay) {
        blessViewClose.addEventListener('click', (e) => {
          e.stopPropagation();
          blessViewOverlay.style.display = 'none';
        });
      }

      if (blindboxQrcodeCloseBtn && blindboxQrcodeOverlay) {
        blindboxQrcodeCloseBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          blindboxQrcodeOverlay.style.display = 'none';
          // 关闭二维码后弹出口令框
          if (passwordOverlay && passwordInput) {
            passwordInput.value = '';
            passwordOverlay.style.display = 'flex';
          }
        });
      }

      if (passwordCancel && passwordOverlay) {
        passwordCancel.addEventListener('click', (e) => {
          e.stopPropagation();
          passwordOverlay.style.display = 'none';
        });
      }

      if (passwordOk && passwordOverlay && passwordInput) {
        passwordOk.addEventListener('click', (e) => {
          e.stopPropagation();
          const pwd = passwordInput.value.trim();
          if (pwd === '666') {
            // 口令正确，关闭口令框，后续逻辑在 Phaser 内部处理
            passwordOverlay.style.display = 'none';
            if (window.onBlindboxPasswordCorrect && typeof window.onBlindboxPasswordCorrect === 'function') {
              window.onBlindboxPasswordCorrect();
            }
          } else {
            // 口令错误，直接关闭
            passwordOverlay.style.display = 'none';
          }
        });
      }
    });

    class LoadingScene extends Phaser.Scene {
      constructor() {
        super('LoadingScene');
      }

      preload() {
        const width = this.cameras.main.width;
        const height = this.cameras.main.height;

        const progressBox = this.add.rectangle(width / 2, height / 2, 320, 50, 0x222222, 0.8);
        const progressBar = this.add.rectangle(width / 2 - 150, height / 2, 0, 30, 0xffd700, 1).setOrigin(0, 0.5);
        const loadingText = this.add.text(width / 2, height / 2 - 50, '加载中...', {
          fontFamily: 'Arial',
          fontSize: '28px',
          color: '#ffffff'
        }).setOrigin(0.5, 0.5);
        const percentText = this.add.text(width / 2, height / 2, '0%', {
          fontFamily: 'Arial',
          fontSize: '24px',
          color: '#ffffff'
        }).setOrigin(0.5, 0.5);

        // 渐显提示文字：在加载过程中循环显示四句圣诞文案
        const tips = [
          '准备好了吗？',
          '圣诞老人驾着车',
          '叮叮当当，叮叮当当',
          '快来许个愿吧！'
        ];
        let tipIndex = 0;
        this.tipRound = 0;
        this.maxTipRound = 3;
        const tipText = this.add.text(width / 2, height / 2 + 80, tips[0], {
          fontFamily: 'Arial',
          fontSize: '24px',
          color: '#ffffff',
          align: 'center',
          wordWrap: { width: 360 }
        }).setOrigin(0.5, 0.5);
        tipText.setAlpha(0);

        const playNextTip = () => {
          const currentTip = tips[tipIndex];
          tipText.setText(currentTip);
          tipText.setAlpha(0);

          this.tweens.add({
            targets: tipText,
            alpha: 1,
            duration: 800,
            onComplete: () => {
              this.time.delayedCall(200, () => {
                this.tweens.add({
                  targets: tipText,
                  alpha: 0,
                  duration: 800,
                  onComplete: () => {
                    tipIndex = (tipIndex + 1) % tips.length;

                    if (tipIndex === 0) {
                      this.tipRound++;
                    }

                    if (this.tipRound < this.maxTipRound) {
                      this.time.delayedCall(200, playNextTip);
                    }
                  }
                });
              });
            }
          });
        };

        playNextTip();

        // 在加载场景底部显示版权信息
        const producerText = this.add.text(width / 2, height - 60, '巧果网络出品', {
          fontFamily: 'Arial',
          fontSize: '20px',
          color: '#cccccc'
        }).setOrigin(0.5, 0.5);

        const urlText = this.add.text(width / 2, height - 30, 'qiaoguokeji.com', {
          fontFamily: 'Arial',
          fontSize: '18px',
          color: '#999999'
        }).setOrigin(0.5, 0.5);

        this.load.on('progress', (value) => {
          progressBar.width = 300 * value;
          percentText.setText(Math.round(value * 100) + '%');
        });

        this.load.on('complete', () => {
          // 资源加载完成后直接进入主场景
          this.isLoaded = true;
          this.time.delayedCall(200, () => {
            this.scene.start('MainScene');
          });
        });

        // 加载圣诞树背景和苹果、音乐（暂时只保留苹果）
        this.load.image('tree_bg', 'assets/images/christmas/tree.png');
        this.load.image('gift_apple', 'assets/images/christmas/apple.png');
        // 彩灯图片
        this.load.image('gift_bulb', 'assets/images/christmas/bulb.png');
        // 盲盒和糖果
        this.load.image('gift_box', 'assets/images/christmas/box.png');
        this.load.image('gift_candy', 'assets/images/christmas/candy.png');
        // 点击引导手势
        this.load.image('hand_hint', 'assets/images/christmas/hand.png');
        // 雪花图片
        this.load.image('snow', 'assets/images/christmas/snow.png');

        // 背景音乐和音效
        this.load.audio('bgm_christmas', ['assets/audio/christmas/christmas.mp3']);
        this.load.audio('sfx_ding', ['assets/audio/christmas/ding.wav']);
        this.load.audio('sfx_success', ['assets/audio/christmas/success.mp3']);
      }

      create() {
        // 标记加载场景已创建
        this.isLoaded = this.isLoaded || false;
      }

      // 备用方法：当需要时启动主场景
      startMainSceneIfReady() {
        if (this.isLoaded) {
          this.scene.start('MainScene');
        }
      }
    }

    class MainScene extends Phaser.Scene {
      constructor() {
        super('MainScene');
      }

      async create() {
        const bg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT / 2, 'tree_bg');
        const scaleX = GAME_WIDTH / bg.width;
        const scaleY = GAME_HEIGHT / bg.height;
        const scale = Math.max(scaleX, scaleY);
        bg.setScale(scale).setScrollFactor(0);

        // 计算圣诞树可挂礼物区域
        this.treeCenterX = GAME_WIDTH / 2;
        this.treeTopY = GAME_HEIGHT * 0.15;
        this.treeBottomY = GAME_HEIGHT * 0.85;
        this.treeMaxHalfWidth = GAME_WIDTH * 0.3;

        // 播放背景音乐
        if (this.sound) {
          this.bgm = this.sound.add('bgm_christmas', {
            loop: true,
            volume: 0.5
          });
          this.bgm.play();
        }

        // 先从后台加载已存在的礼物（苹果 + 糖果）
        try {
          const apples = await fetchChristmasApples();
          if (Array.isArray(apples)) {
            apples.forEach((item) => {
              const x = parseInt(item.x, 10);
              const y = parseInt(item.y, 10);
              if (isNaN(x) || isNaN(y)) return;

              const bless = (item.bless || '').toString();
              const appleId = (item.apple_id || '').toString();

              let textureKey = 'gift_apple';
              if (appleId.startsWith('candy_')) {
                textureKey = 'gift_candy';
              }

              this.spawnGiftAtPosition(textureKey, x, y, bless);
            });
          }
        } catch (e) {
          console.error('初始化加载苹果失败', e);
        }

        // 在圣诞树上挂一圈闪烁的彩灯
        this.createBulbLights();

        // 创建右上角盲盒按钮
        this.createBlindboxButton();

        this.createGiftButtons();

        // 启动雪花效果
        this.createSnowEffect();
      }

      createGiftButtons() {
        const gifts = [
          { key: 'gift_apple', label: '\u70b9\u82f9\u679c\u6302\u795d\u798f' }
        ];

        // 将苹果按钮移动到原来盲盒按钮的大致位置（右上角）
        const margin = 100;
        const buttonX = GAME_WIDTH - margin - 40;
        const buttonY = margin + 80;

        gifts.forEach((gift) => {
          const x = buttonX;
          const y = buttonY;
          // 苹果按钮（无边框），通过轻微呼吸动画引导点击
          const thumb = this.add.image(x, y, gift.key);
          const baseScale = 0.4; // 按钮上的苹果更大一些
          const scale = Math.min(
            (GAME_WIDTH * 0.24) / thumb.width,
            (GAME_HEIGHT * 0.14) / thumb.height
          );
          thumb.setScale(Math.min(baseScale, scale));

          // 慢速呼吸动画，引导玩家点击
          this.tweens.add({
            targets: thumb,
            scaleX: thumb.scaleX * 1.05,
            scaleY: thumb.scaleY * 1.05,
            yoyo: true,
            repeat: -1,
            duration: 1000,
            ease: 'Sine.easeInOut'
          });

          // 在按钮左下方添加点击手势提示
          const handOffsetX = GAME_WIDTH * 0.07;
          const handOffsetY = GAME_HEIGHT * 0.035;
          const hand = this.add.image(x - handOffsetX, y + handOffsetY, 'hand_hint');
          const handScale = 0.5;
          hand.setScale(handScale);

          thumb.setInteractive({ useHandCursor: true })
            .on('pointerdown', async () => {
              // 播放点击按钮音效
              if (this.sound) {
                this.sound.play('sfx_ding', { volume: 0.9 });
              }
              // 随机生成一个苹果目标位置（飞行动画的终点）
              const targetPosition = this.generateRandomApplePosition();

              // 记录当前礼物挂载的动画信息：起点为按钮位置，终点为随机生成的位置
              this.currentGiftAnim = {
                start: { x: thumb.x, y: thumb.y },
                end: { x: targetPosition.x, y: targetPosition.y },
                key: gift.key
              };

              // 弹出祝福语编辑弹窗
              const blessEditOverlay = document.getElementById('bless-edit-overlay');
              const blessEditText = document.getElementById('bless-edit-text');
              if (blessEditOverlay && blessEditText) {
                blessEditText.value = '';
                blessEditOverlay.style.display = 'flex';

                // 在 window 上暂存一个回调，点击“挂上苹果”时执行
                window.currentBlessEdit = {
                  onConfirm: async () => {
                    const bless = blessEditText.value.trim();

                    // 如果当前没有记录动画信息，则兜底使用随机位置
                    const animInfo = this.currentGiftAnim || {
                      start: { x: thumb.x, y: thumb.y },
                      end: this.generateRandomApplePosition(),
                      key: gift.key
                    };

                    const payload = {
                      apple_id: 'apple_' + Date.now() + '_' + Math.floor(Math.random() * 1000000),
                      x: Math.round(animInfo.end.x),
                      y: Math.round(animInfo.end.y),
                      bless: bless
                    };

                    const result = await saveChristmasApplePosition(payload);
                    if (result) {
                      // 挂载成功音效
                      if (this.sound) {
                        this.sound.play('sfx_success', { volume: 0.9 });
                      }

                      // 先从起点到终点播放飞行动画
                      const flyApple = this.add.image(animInfo.start.x, animInfo.start.y, animInfo.key);
                      flyApple.setScale(thumb.scaleX);

                      this.tweens.add({
                        targets: flyApple,
                        x: animInfo.end.x,
                        y: animInfo.end.y,
                        duration: 700,
                        ease: 'Cubic.easeInOut',
                        onComplete: () => {
                          flyApple.destroy();
                          // 飞行完成后，在终点挂上真正的苹果（带晃动动画和祝福语）
                          const finalGift = this.spawnGiftAtPosition(animInfo.key, payload.x, payload.y, bless);

                          // 画面周围暂时暗下去，苹果周围出现金光高亮
                          const cam = this.cameras.main;

                          // 全屏暗化遮罩
                          const darkOverlay = this.add.rectangle(
                            cam.width / 2,
                            cam.height / 2,
                            cam.width,
                            cam.height,
                            0x000000,
                            0.7
                          );
                          darkOverlay.setScrollFactor(0);

                          // 在苹果位置创建动态放射线金光
                          const rays = this.add.graphics();
                          const rayCount = 16;
                          const innerR = Math.min(finalGift.displayWidth, finalGift.displayHeight) * 0.3;
                          const outerR = Math.max(finalGift.displayWidth, finalGift.displayHeight) * 0.9;
                          rays.setAlpha(0);

                          const drawRays = () => {
                            rays.clear();
                            rays.lineStyle(3, 0xffd700, 1);
                            for (let i = 0; i < rayCount; i++) {
                              const angle = (Math.PI * 2 * i) / rayCount;
                              const x1 = finalGift.x + Math.cos(angle) * innerR;
                              const y1 = finalGift.y + Math.sin(angle) * innerR;
                              const x2 = finalGift.x + Math.cos(angle) * outerR;
                              const y2 = finalGift.y + Math.sin(angle) * outerR;
                              rays.beginPath();
                              rays.moveTo(x1, y1);
                              rays.lineTo(x2, y2);
                              rays.strokePath();
                            }
                          };

                          drawRays();

                          // 渐显并闪一下，营造金光四射感（不旋转，保证始终环绕苹果）
                          rays.alpha = 0;

                          this.tweens.add({
                            targets: rays,
                            alpha: 1,
                            duration: 300,
                            yoyo: true
                          });

                          // 一段时间后淡出暗幕和光效
                          this.time.delayedCall(800, () => {
                            this.tweens.add({
                              targets: [darkOverlay, rays],
                              alpha: 0,
                              duration: 400,
                              onComplete: () => {
                                darkOverlay.destroy();
                                rays.destroy();
                              }
                            });
                          });
                        }
                      });

                      blessEditOverlay.style.display = 'none';
                      window.currentBlessEdit = null;
                      this.currentGiftAnim = null;
                    }
                  }
                };
              }

              this.tweens.add({
                targets: thumb,
                scaleX: thumb.scaleX * 0.9,
                scaleY: thumb.scaleY * 0.9,
                yoyo: true,
                duration: 120
              });
            });

          this.add.text(x, y + 40, gift.label, {
            fontFamily: 'Arial',
            fontSize: '22px',
            color: '#ffffff',
            stroke: '#000000',
            strokeThickness: 3
          }).setOrigin(0.5, 0.0);
        });
      }

      // 创建右上角的盲盒 box 按钮
      createBlindboxButton() {
        const margin = 100;
        const x = GAME_WIDTH - margin - 40;
        // 盲盒按钮整体下移一段距离，避免与苹果按钮重叠
        const y = margin + 80 + 180;

        const box = this.add.image(x, y, 'gift_box');
        box.setScale(0.5);
        box.setInteractive({ useHandCursor: true })
          .on('pointerdown', () => {
            // 按钮点击音效
            if (this.sound) {
              this.sound.play('sfx_ding', { volume: 0.9 });
            }

            const blindboxQrcodeOverlay = document.getElementById('blindbox-qrcode-overlay');
            if (blindboxQrcodeOverlay) {
              blindboxQrcodeOverlay.style.display = 'flex';
            }
          });

        // 盲盒按钮隔一段时间自动抖动一下
        const baseScale = box.scale;
        const shakeInterval = 5000; // 每隔 5 秒抖一次
        const shakeDuration = 500;  // 抖动持续 0.5 秒

        this.time.addEvent({
          delay: shakeInterval,
          loop: true,
          callback: () => {
            // 先还原缩放，防止叠加
            box.setScale(baseScale);

            // 做一个左右轻微抖动 + 轻微缩放的 tween
            this.tweens.add({
              targets: box,
              scaleX: baseScale * 1.08,
              scaleY: baseScale * 1.08,
              x: { from: x - 6, to: x + 6 },
              duration: shakeDuration / 2,
              yoyo: true,
              repeat: 1,
              onComplete: () => {
                box.setScale(baseScale);
                box.x = x;
              }
            });
          }
        });

        // 供口令校验成功后调用的回调
        const scene = this;
        window.onBlindboxPasswordCorrect = async function () {
          // 显示祝福语编辑弹窗，复用现有 UI
          const blessEditOverlay = document.getElementById('bless-edit-overlay');
          const blessEditText = document.getElementById('bless-edit-text');
          if (!blessEditOverlay || !blessEditText) return;

          blessEditText.value = '';
          blessEditOverlay.style.display = 'flex';

          // 随机生成一个糖果位置
          const position = scene.generateRandomApplePosition();

          window.currentBlessEdit = {
            onConfirm: async () => {
              const bless = blessEditText.value.trim();

              const payload = {
                apple_id: 'candy_' + Date.now() + '_' + Math.floor(Math.random() * 1000000),
                x: Math.round(position.x),
                y: Math.round(position.y),
                bless: bless
              };

              const result = await saveChristmasApplePosition(payload);
              if (result) {
                // 挂载成功音效
                if (scene.sound) {
                  scene.sound.play('sfx_success', { volume: 0.9 });
                }
                // 在树上挂载糖果
                scene.spawnGiftAtPosition('gift_candy', payload.x, payload.y, bless);
                blessEditOverlay.style.display = 'none';
                window.currentBlessEdit = null;
              }
            }
          };
        };
      }

      // 生成一个位于圣诞树区域内的随机坐标（仅计算，不直接绘制）
      generateRandomApplePosition() {
        const y = Phaser.Math.Between(this.treeTopY, this.treeBottomY);
        const t = (y - this.treeTopY) / (this.treeBottomY - this.treeTopY);
        const halfWidth = this.treeMaxHalfWidth * (0.2 + 0.8 * t);
        const x = Phaser.Math.Between(
          Math.floor(this.treeCenterX - halfWidth),
          Math.floor(this.treeCenterX + halfWidth)
        );
        return { x, y };
      }

      // 在给定坐标处绘制一个苹果，并添加轻微晃动动画，可附带祝福语
      spawnGiftAtPosition(giftKey, x, y, blessText = '') {
        const gift = this.add.image(x, y, giftKey);
        const scale = 0.45; // 树上的苹果稍微大一些
        gift.setScale(scale);

        gift.setDepth(10 + y);

        gift.setRotation(Phaser.Math.FloatBetween(-0.15, 0.15));

        // 将祝福语挂在 sprite 对象上
        gift.blessText = blessText || '';

        // 点击苹果时弹出祝福语查看弹窗
        gift.setInteractive({ useHandCursor: true })
          .on('pointerdown', () => {
            const blessViewOverlay = document.getElementById('bless-view-overlay');
            const blessViewText = document.getElementById('bless-view-text');
            if (blessViewOverlay && blessViewText) {
              blessViewText.textContent = gift.blessText || '圣诞快乐！';
              blessViewOverlay.style.display = 'flex';
            }
          });

        this.tweens.add({
          targets: gift,
          y: y - 8,
          duration: 600,
          yoyo: true,
          repeat: -1,
          ease: 'Sine.easeInOut'
        });

        return gift;
      }

      // 在圣诞树上挂一圈闪烁的彩灯
      createBulbLights() {
        const levels = 5; // 分成若干层
        for (let i = 0; i < levels; i++) {
          const t = i / (levels - 1); // 从树顶到树底的插值 0-1
          const y = this.treeTopY + t * (this.treeBottomY - this.treeTopY);
          const halfWidth = this.treeMaxHalfWidth * (0.2 + 0.8 * t);

          const bulbsPerLevel = 5;
          for (let j = 0; j < bulbsPerLevel; j++) {
            const x = Phaser.Math.Linear(
              this.treeCenterX - halfWidth,
              this.treeCenterX + halfWidth,
              j / (bulbsPerLevel - 1)
            );

            const bulb = this.add.image(x, y, 'gift_bulb');
            const scale = 0.18;
            bulb.setScale(scale);
            bulb.setDepth(5 + y); // 略低于苹果

            // 随机初始透明度，让闪烁错开
            bulb.setAlpha(Phaser.Math.FloatBetween(0.4, 1));

            // 闪烁效果：透明度和微小缩放往复变化
            this.tweens.add({
              targets: bulb,
              alpha: { from: bulb.alpha, to: Phaser.Math.FloatBetween(0.3, 1) },
              scaleX: scale * Phaser.Math.FloatBetween(0.9, 1.1),
              scaleY: scale * Phaser.Math.FloatBetween(0.9, 1.1),
              yoyo: true,
              repeat: -1,
              duration: Phaser.Math.Between(800, 1600),
              delay: Phaser.Math.Between(0, 800),
              ease: 'Sine.easeInOut'
            });
          }
        }
      }

      // 创建持续飘落的雪花效果
      createSnowEffect() {
        // 定时生成雪花
        this.time.addEvent({
          delay: 300,
          loop: true,
          callback: () => {
            this.spawnSnowflake();
          }
        });
      }

      // 生成单个雪花，从顶部缓慢飘落到底部
      spawnSnowflake() {
        const startX = Phaser.Math.Between(0, GAME_WIDTH);
        const startY = -Phaser.Math.Between(10, 100);

        const snow = this.add.image(startX, startY, 'snow');
        const scale = Phaser.Math.FloatBetween(0.15, 0.4);
        snow.setScale(scale);
        snow.setAlpha(Phaser.Math.FloatBetween(0.6, 1));

        // 随机左右轻微偏移
        const endX = startX + Phaser.Math.Between(-80, 80);
        const endY = GAME_HEIGHT + 100;
        const duration = Phaser.Math.Between(5000, 9000);

        this.tweens.add({
          targets: snow,
          x: endX,
          y: endY,
          alpha: 0,
          duration: duration,
          ease: 'Linear',
          onComplete: () => {
            snow.destroy();
          }
        });
      }
    }

    const config = {
      type: Phaser.AUTO,
      width: GAME_WIDTH,
      height: GAME_HEIGHT,
      parent: 'game-container',
      backgroundColor: '#000000',
      scale: {
        mode: Phaser.Scale.ENVELOP,
        autoCenter: Phaser.Scale.CENTER_BOTH
      },
      scene: [LoadingScene, MainScene]
    };

    const game = new Phaser.Game(config);
    window.game = game;
  </script>
</body>
</html>