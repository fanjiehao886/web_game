<?php
include_once 'conn.php';
require_once 'jssdk.php';
$jssdk = new JSSDK('wx6e430c2f6cf05ae9', '496df986ec85edda2e7e5432b06e4c19');
$signPackage = $jssdk->GetSignPackage();

// 为游戏分享准备标题和关键词
$title = '在线烧香 - 财神保佑';
$keywords = ['在线烧香', '财神保佑', '求好运'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <title>在线烧香 - 财神保佑</title>
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
  <!-- 使用 CDN 引入 Phaser3，如需本地部署可改成本地路径 -->
  <script src="assets/js/phaser.min.js"></script>
</head>
<body>
  <div id="game-container"></div>

  <!-- 分享弹窗容器（覆盖在画布上方） -->
  <div id="share-overlay" style="
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
      <button id="share-close-btn" style="
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
        分享给朋友，获得更多香火！
      </div>
      <div style="position:relative;display:inline-block;">
        <img id="share-img" src="assets/images/caishen/share_timeline.png" alt="分享图片" style="width:60vw;max-width:300px;border-radius:8px;" />
      </div>
    </div>
  </div>

  <!-- 普通 HTML 二维码弹窗容器（覆盖在画布上方） -->
  <div id="qrcode-overlay" style="
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
      <button id="qrcode-close-btn" style="
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
        长按识别二维码关注"狼山信息"，关注后即可获得1根香！
      </div>
      <div style="position:relative;display:inline-block;">
        <img id="qrcode-img" src="assets/images/caishen/qrcode.png" alt="二维码" style="width:60vw;max-width:300px;" />
      </div>
    </div>
  </div>

  <script>
    // 初始化关闭按钮功能
    function initCloseButton() {
      const closeBtn = document.getElementById('qrcode-close-btn');
      const shareCloseBtn = document.getElementById('share-close-btn');
      let popupOpenTime = 0; // 记录弹窗打开时间
      
      // 记录弹窗打开时间
      function recordPopupOpenTime() {
        popupOpenTime = Date.now();
        console.log('弹窗打开时间:', popupOpenTime);
      }
      
      // 二维码关闭按钮点击事件 - 检查时间后赠送香（仅前两次有效）
      closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const overlay = document.getElementById('qrcode-overlay');
        overlay.style.display = 'none';
        
        // 检查弹窗显示时间（仅 burnClickCount < 3 时才可能送香）
        let shouldGiveIncense = false;
        if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
          const sceneForClick = window.game.scene.getScene('MainScene');
          const displayTime = Date.now() - popupOpenTime;
          // 只有第 1、2 次点击烧香时才允许送香
          if (sceneForClick.burnClickCount < 3 && displayTime >= 5000) {
            shouldGiveIncense = true;
          }
          console.log('弹窗显示时间:', displayTime + 'ms', '点击次数:', sceneForClick.burnClickCount, '是否赠送香:', shouldGiveIncense);
        }
        
        // 隐藏Phaser遮罩并赠送香（如果满足条件）
        if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
          const scene = window.game.scene.getScene('MainScene');
          scene.closeQrcodePopup();
          scene.onQrcodePopupClosed(shouldGiveIncense);
        }
      });

      // 分享关闭按钮点击事件 - 直接关闭，不赠送香
      shareCloseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const overlay = document.getElementById('share-overlay');
        overlay.style.display = 'none';
        
        // 隐藏Phaser遮罩
        if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
          const scene = window.game.scene.getScene('MainScene');
          scene.closeSharePopup();
        }
      });

      // 监听弹窗显示事件
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            const qrcodeOverlay = document.getElementById('qrcode-overlay');
            const shareOverlay = document.getElementById('share-overlay');
            
            // 监听二维码弹窗显示
            if (qrcodeOverlay && qrcodeOverlay.style.display === 'flex') {
              recordPopupOpenTime();
            }
            
            // 监听分享弹窗显示 - 5秒后自动赠送香
            if (shareOverlay && shareOverlay.style.display === 'flex') {
              console.log('分享弹窗显示，5秒后自动赠送香');
              setTimeout(() => {
                // 检查弹窗是否仍然显示
                if (shareOverlay.style.display === 'flex') {
                  console.log('5秒时间到，自动赠送香并关闭分享弹窗');
                  
                  // 先隐藏HTML弹窗
                  shareOverlay.style.display = 'none';
                  
                  // 尝试多种方式获取Phaser场景
                  console.log('window.game存在:', !!window.game);
                  console.log('window.game.scene存在:', !!(window.game && window.game.scene));
                  
                  let scene = null;
                  if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                    scene = window.game.scene.getScene('MainScene');
                    console.log('通过getScene获取到场景');
                  } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                    scene = window.game.scene.keys['MainScene'];
                    console.log('通过scene.keys获取到场景');
                  } else {
                    console.log('无法获取到Phaser场景，尝试延迟重试');
                    // 延迟100ms重试
                    setTimeout(() => {
                      let retryScene = null;
                      if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                        retryScene = window.game.scene.getScene('MainScene');
                        console.log('延迟重试成功，通过getScene获取到场景');
                      } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                        retryScene = window.game.scene.keys['MainScene'];
                        console.log('延迟重试成功，通过scene.keys获取到场景');
                      } else {
                        console.log('延迟重试仍然失败，直接隐藏遮罩');
                        // 直接尝试隐藏遮罩 - 使用更直接的方式
                        console.log('尝试直接访问game对象:', window.game);
                        if (window.game) {
                          console.log('game对象存在，尝试访问scenes:', window.game.scene);
                          if (window.game.scene && window.game.scene.scenes) {
                            console.log('scenes数组存在，长度:', window.game.scene.scenes.length);
                            // 遍历所有场景找到MainScene
                            for (let i = 0; i < window.game.scene.scenes.length; i++) {
                              const scene = window.game.scene.scenes[i];
                              console.log('场景', i, ':', scene.scene.key);
                              if (scene.scene.key === 'MainScene') {
                                console.log('找到MainScene，直接隐藏遮罩');
                                if (scene.popupContainer) {
                                  scene.popupContainer.setVisible(false);
                                  scene.isPopupOpen = false;
                                  console.log('直接隐藏遮罩成功');
                                  // 尝试赠送香
                                  scene.onSharePopupClosed();
                                }
                                return;
                              }
                            }
                          }
                        }
                        console.log('所有方式都失败，无法隐藏遮罩');
                        return;
                      }
                      
                      if (retryScene) {
                        console.log('延迟重试：调用closeSharePopup');
                        retryScene.closeSharePopup();
                        console.log('延迟重试：调用onSharePopupClosed');
                        retryScene.onSharePopupClosed();
                      }
                    }, 100);
                    return;
                  }
                  
                  if (scene) {
                    console.log('调用closeSharePopup');
                    scene.closeSharePopup(); // 关闭Phaser遮罩
                    console.log('调用onSharePopupClosed');
                    scene.onSharePopupClosed(); // 赠送香
                  }
                }
              }, 5000); // 5秒
            }
          }
        });
      });

      // 开始观察弹窗元素
      const qrcodeOverlay = document.getElementById('qrcode-overlay');
      const shareOverlay = document.getElementById('share-overlay');
      
      if (qrcodeOverlay) {
        observer.observe(qrcodeOverlay, { attributes: true });
        
        // 如果二维码弹窗已经显示，立即记录时间
        if (qrcodeOverlay.style.display === 'flex') {
          recordPopupOpenTime();
        }
      }
      
      if (shareOverlay) {
        observer.observe(shareOverlay, { attributes: true });
        
        // 如果分享弹窗已经显示，立即启动定时器
        if (shareOverlay.style.display === 'flex') {
          console.log('分享弹窗显示，5秒后自动赠送香');
          setTimeout(() => {
            if (shareOverlay.style.display === 'flex') {
              console.log('5秒时间到，自动赠送香并关闭分享弹窗');
              
              // 先隐藏HTML弹窗
              shareOverlay.style.display = 'none';
              
              // 尝试多种方式获取Phaser场景
              console.log('window.game存在:', !!window.game);
              console.log('window.game.scene存在:', !!(window.game && window.game.scene));
              
              let scene = null;
              if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                scene = window.game.scene.getScene('MainScene');
                console.log('通过getScene获取到场景');
              } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                scene = window.game.scene.keys['MainScene'];
                console.log('通过scene.keys获取到场景');
              } else {
                console.log('无法获取到Phaser场景，尝试延迟重试');
                // 延迟100ms重试
                setTimeout(() => {
                  let retryScene = null;
                  if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                    retryScene = window.game.scene.getScene('MainScene');
                    console.log('延迟重试成功，通过getScene获取到场景');
                  } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                    retryScene = window.game.scene.keys['MainScene'];
                    console.log('延迟重试成功，通过scene.keys获取到场景');
                  } else {
                    console.log('延迟重试仍然失败，直接隐藏遮罩');
                    // 直接尝试隐藏遮罩 - 使用更直接的方式
                    console.log('尝试直接访问game对象:', window.game);
                    if (window.game) {
                      console.log('game对象存在，尝试访问scenes:', window.game.scene);
                      if (window.game.scene && window.game.scene.scenes) {
                        console.log('scenes数组存在，长度:', window.game.scene.scenes.length);
                        // 遍历所有场景找到MainScene
                        for (let i = 0; i < window.game.scene.scenes.length; i++) {
                          const scene = window.game.scene.scenes[i];
                          console.log('场景', i, ':', scene.scene.key);
                          if (scene.scene.key === 'MainScene') {
                            console.log('找到MainScene，直接隐藏遮罩');
                            if (scene.popupContainer) {
                              scene.popupContainer.setVisible(false);
                              scene.isPopupOpen = false;
                              console.log('直接隐藏遮罩成功');
                              // 尝试赠送香
                              scene.onSharePopupClosed();
                            }
                            return;
                          }
                        }
                      }
                    }
                    console.log('所有方式都失败，无法隐藏遮罩');
                    return;
                  }
                  
                  if (retryScene) {
                    console.log('延迟重试：调用closeSharePopup');
                    retryScene.closeSharePopup();
                    console.log('延迟重试：调用onSharePopupClosed');
                    retryScene.onSharePopupClosed();
                  }
                }, 100);
                return;
              }
              
              if (scene) {
                console.log('调用closeSharePopup');
                scene.closeSharePopup();
                console.log('调用onSharePopupClosed');
                scene.onSharePopupClosed();
              }
            }
          }, 5000);
        }
      }
    }
    
    // 页面加载完成后初始化
    document.addEventListener('DOMContentLoaded', initCloseButton);
    
    const GAME_WIDTH = 720;
    const GAME_HEIGHT = 1280;

    // 简单加载场景：显示进度条和百分比
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

        // 渐显提示文字：在加载过程中循环显示四句话
        const tips = [
          '从现在开始，进入祈福',
          '你准备好了吗？',
          '香为佛使，紫气东来',
          '传说，关注狼山信息网，祈福更加灵验'
        ];
        let tipIndex = 0;
        // 轮播控制：tipRound 表示当前已经播放了多少轮，maxTipRound 表示最多播放多少轮
        this.tipRound = 0;
        this.maxTipRound = 3; // 如需多轮，改成 2 或 3
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

          // 淡入 -> 停留 -> 淡出，然后切到下一句
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
                    // 切到下一句
                    tipIndex = (tipIndex + 1) % tips.length;

                    // 每回到第一句，说明播完一整轮
                    if (tipIndex === 0) {
                      this.tipRound++;
                    }

                    // 只要还没播完设定的轮数，就继续播下一句
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

        // 在加载场景底部显示“巧果网络出品”
        const producerText = this.add.text(width / 2, height - 60, '巧果网络出品', {
          fontFamily: 'Arial',
          fontSize: '20px',
          color: '#cccccc'
        }).setOrigin(0.5, 0.5);
        // 在其下方显示网址
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
          this.time.delayedCall(200, () => {
            this.scene.start('MainScene');
          });
        });

        // 在这里加载所有游戏资源
        this.load.image('bg_caishen', 'assets/images/caishen/bg_caishen.png');
        this.load.image('altar', 'assets/images/caishen/altar.png');
        this.load.image('incense_burner', 'assets/images/caishen/incense_burner.png');
        this.load.image('incense_unlit', 'assets/images/caishen/incense_unlit.png');
        this.load.image('incense_lit', 'assets/images/caishen/incense_lit.png');
        this.load.image('btn_burn', 'assets/images/caishen/btn_burn.png');
        this.load.image('popup_bg', 'assets/images/caishen/popup_bg.png');
        this.load.image('qrcode', 'assets/images/caishen/qrcode.png');
        this.load.image('btn_close', 'assets/images/caishen/btn_close.png');
        // 金币图片
        this.load.image('coin', 'assets/images/caishen/coin.png');

        // 音效资源（使用 wav 格式）
        this.load.audio('burn_sound', ['assets/audio/caishen/burn_sound.wav']);
        this.load.audio('bgm_temple', ['assets/audio/caishen/bgm_temple.mp3']);
        // 金币掉落音效
        this.load.audio('coin_drop', ['assets/audio/caishen/drop.mp3']);
      }

      create() {
        // 这里无需额外逻辑，complete 事件会切换到 MainScene
      }
    }

    class MainScene extends Phaser.Scene {
      constructor() {
        super('MainScene');
      }

      create() {
        // 玩家进入游戏时没有香
        this.availableIncense = 0;
        this.isPopupOpen = false;
        this.awardIncenseOnPopupClose = true;
        this.burnClickCount = 0; // 添加点击计数器

        // 背景（铺满屏幕）
        const bg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT / 2, 'bg_caishen');
        const scaleX = GAME_WIDTH / bg.width;
        const scaleY = GAME_HEIGHT / bg.height;
        const scale = Math.max(scaleX, scaleY);
        bg.setScale(scale).setScrollFactor(0);

        // 香案与香炉
        const altar = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.76, 'altar');
        altar.setOrigin(0.5, 0.5);
        altar.setScale(0.4);

        const burner = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.7, 'incense_burner');
        burner.setOrigin(0.5, 0.5);
        burner.setScale(0.5);

        // 香（默认未点燃）
        this.incenseSprite = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.67, 'incense_unlit');
        this.incenseSprite.setOrigin(0.5, 1);

        // 可用香数量文字
        this.incenseText = this.add.text(
          GAME_WIDTH / 2,
          GAME_HEIGHT * 0.1,
          '可用香：' + this.availableIncense,
          {
            fontFamily: 'Arial',
            fontSize: '40px',
            color: '#ffe7a6',
            stroke: '#000000',
            strokeThickness: 4,
            align: 'center'
          }
        ).setOrigin(0.5, 0.5);

        // 提示说明
        this.tipText = this.add.text(
          GAME_WIDTH / 2,
          GAME_HEIGHT * 0.16,
          '恭祝大家新年快乐，万事如意！',
          {
            fontFamily: 'Arial',
            fontSize: '26px',
            color: '#ffffff',
            align: 'center',
            wordWrap: { width: GAME_WIDTH * 0.9 }
          }
        ).setOrigin(0.5, 0.5);

        // 烧香按钮
        this.burnBtn = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.9, 'btn_burn');
        this.burnBtn.setScale(0.5);
        this.burnBtn.setInteractive({ useHandCursor: true })
          .on('pointerdown', () => {
            this.handleBurnClick();
          });

        // 顶部提示（如领取成功），默认隐藏
        this.toastText = this.add.text(
          GAME_WIDTH / 2,
          GAME_HEIGHT * 0.25,
          '',
          {
            fontFamily: 'Arial',
            fontSize: '32px',
            color: '#ffff99',
            stroke: '#000000',
            strokeThickness: 4,
            align: 'center',
            wordWrap: { width: GAME_WIDTH * 0.8 }
          }
        ).setOrigin(0.5, 0.5);
        this.toastText.setAlpha(0);

        // 创建 Phaser 内部的半透明遮罩，仅用于暗背景（不放真实二维码逻辑）
        this.createQrcodeMaskOnly();

        // 播放背景音乐（循环播放）
        if (!this.bgm && this.sound) {
          this.bgm = this.sound.add('bgm_temple', {
            loop: true,
            volume: 0.5
          });
          this.bgm.play();
        }

        // 进入游戏后立刻弹出二维码窗口
        this.openQrcodePopup();
      }

      onQrcodePopupClosed(shouldGiveIncense = false) {
        if (shouldGiveIncense) {
          // 弹窗显示超过5秒，赠送香
          this.availableIncense = (this.availableIncense || 0) + 1;
          this.updateIncenseText();
          this.showToast('获得1根香！');
        } else {
          // 弹窗显示时间不足5秒，不赠送香
          this.showToast('恳求关注公众号，获取更多香火');
        }
      }

      onSharePopupClosed() {
        // 分享弹窗5秒后自动赠送香
        console.log('onSharePopupClosed被调用，开始赠送香');
        this.availableIncense = (this.availableIncense || 0) + 1;
        this.updateIncenseText();
        console.log('香数量已更新:', this.availableIncense);
        this.showToast('分享成功！获得1根香！');
      }

      playBurnAnimation() {
        // 切换为点燃的香
        this.incenseSprite.setTexture('incense_lit');
        this.incenseSprite.setScale(0.2);

        // 播放点香音效
        if (this.sound) {
          const sfx = this.sound.play('burn_sound', { volume: 0.8 });
          // 300 表示 0.3 秒，你可以改成 500 (0.5 秒)
          this.time.delayedCall(300, () => {
            if (sfx && sfx.stop) {
              sfx.stop();
            }
          });
        }

        // 轻微上下浮动模拟燃烧
        this.tweens.add({
          targets: this.incenseSprite,
          y: this.incenseSprite.y - 10,
          duration: 500,
          yoyo: true,
          repeat: 5
        });
        // 金币雨动画和掉落声音
        this.createCoinRain();

        // 可选音效
        // this.sound.play('burn_sound');
      }

      createCoinRain() {
        if (!this.add || !this.tweens || !this.sound || !this.time) {
          return;
        }

        // 1. 先立刻创建金币雨动画（视觉优先）
        const coinCount = 20;
        for (let i = 0; i < coinCount; i++) {
          const startX = Math.random() * GAME_WIDTH;
          const startY = GAME_HEIGHT * -0.2 - Math.random() * 200;

          const coin = this.add.image(startX, startY, 'coin');
          coin.setScale(0.2 + Math.random() * 0.3);

          const endY = GAME_HEIGHT * 0.75 + Math.random() * 200;
          const duration = 3000 + Math.random() * 600;

          this.tweens.add({
            targets: coin,
            y: endY,
            angle: 360,
            duration: duration,
            ease: 'Cubic.easeIn',
            onComplete: () => {
              coin.destroy();
            }
          });
        }

        // 2. 1 秒后再开始播放金币掉落声音
        const soundDelayMs = 1000;
        this.time.delayedCall(soundDelayMs, () => {
          // 掉落声音重复播放 3 遍
          for (let i = 0; i < 3; i++) {
            this.time.delayedCall(i * 400, () => {
              this.sound.play('coin_drop', { volume: 0.9 });
            });
          }
        });
      }

      // 仅在 Phaser 中创建一个暗色遮罩（让背景变暗），主二维码用 HTML img 负责
      createQrcodeMaskOnly() {
        console.log('创建Phaser遮罩容器');
        this.popupContainer = this.add.container(0, 0);

        const mask = this.add.rectangle(0, 0, GAME_WIDTH, GAME_HEIGHT, 0x000000, 0.6)
          .setOrigin(0, 0)
          .setInteractive();

        this.popupContainer.add(mask);
        this.popupContainer.setVisible(false);
        console.log('Phaser遮罩容器创建完成，初始状态为隐藏');
      }

      openQrcodePopup() {
        if (this.isPopupOpen) return;
        this.isPopupOpen = true;
        
        // 显示 HTML 覆盖层
        const overlay = document.getElementById('qrcode-overlay');
        if (overlay) {
          overlay.style.display = 'flex';
        }
        
        // 显示 Phaser 弹窗容器
        this.popupContainer.setVisible(true);
      }

      openSharePopup() {
        console.log('openSharePopup被调用');
        if (this.isPopupOpen) return;
        this.isPopupOpen = true;
        
        // 显示分享 HTML 覆盖层
        const overlay = document.getElementById('share-overlay');
        if (overlay) {
          overlay.style.display = 'flex';
          console.log('分享HTML弹窗已显示');
        }
        
        // 显示 Phaser 弹窗容器
        if (this.popupContainer) {
          this.popupContainer.setVisible(true);
          console.log('Phaser遮罩已显示，visible:', this.popupContainer.visible);
        } else {
          console.log('警告: popupContainer不存在');
        }
      }

      closeQrcodePopup() {
        this.isPopupOpen = false;
        this.popupContainer.setVisible(false);

        const overlay = document.getElementById('qrcode-overlay');
        if (overlay) {
          overlay.style.display = 'none';
        }
      }

      closeSharePopup() {
        console.log('closeSharePopup被调用，开始关闭Phaser遮罩');
        console.log('关闭前 - isPopupOpen:', this.isPopupOpen);
        console.log('关闭前 - popupContainer visible:', this.popupContainer ? this.popupContainer.visible : 'popupContainer不存在');
        
        this.isPopupOpen = false;
        
        // 确保Phaser遮罩被隐藏
        if (this.popupContainer) {
          this.popupContainer.setVisible(false);
          console.log('关闭后 - popupContainer visible:', this.popupContainer.visible);
        } else {
          console.log('警告: popupContainer不存在');
        }
        
        console.log('关闭后 - isPopupOpen:', this.isPopupOpen);
      }

      showToast(message) {
        this.toastText.setText(message);
        this.toastText.setAlpha(1);
        this.tweens.killTweensOf(this.toastText);
        this.tweens.add({
          targets: this.toastText,
          alpha: 0,
          duration: 2000,
          ease: 'Linear',
          delay: 1000
        });
      }
      
      updateIncenseText() {
        this.incenseText.setText('可用香：' + this.availableIncense);
      }
      
      handleBurnClick() {
        this.burnClickCount++; // 增加点击计数
        
        if (this.availableIncense > 0) {
          this.availableIncense--;
          this.updateIncenseText();
          this.playBurnAnimation();
        } else {
          // 第一次点击显示二维码弹窗
          if (this.burnClickCount === 1) {
            this.openQrcodePopup();
          } else if (this.burnClickCount === 2) {
            // 第二次点击显示分享弹窗
            this.openSharePopup();
          } else {
            // 第三次及以后点击：继续显示二维码弹窗（但不会再赠送香）
            this.openQrcodePopup();
          }
        }
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
    
    // 将游戏对象暴露到全局，供其他函数使用
    window.game = game;

    // 绑定 HTML 关闭按钮：点击关闭弹窗并检查显示时间
    (function bindHtmlCloseButton() {
      let popupOpenTime = 0;
      
      // 记录弹窗打开时间
      function recordPopupOpenTime() {
        popupOpenTime = Date.now();
      }
      
      // 监听弹窗显示
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            const qrcodeOverlay = document.getElementById('qrcode-overlay');
            const shareOverlay = document.getElementById('share-overlay');
            
            // 监听二维码弹窗显示
            if (qrcodeOverlay && qrcodeOverlay.style.display === 'flex') {
              recordPopupOpenTime();
            }
            
            // 监听分享弹窗显示 - 5秒后自动赠送香
            if (shareOverlay && shareOverlay.style.display === 'flex') {
              console.log('分享弹窗显示，5秒后自动赠送香');
              setTimeout(() => {
                // 检查弹窗是否仍然显示
                if (shareOverlay.style.display === 'flex') {
                  console.log('5秒时间到，自动赠送香并关闭分享弹窗');
                  
                  // 先隐藏HTML弹窗
                  shareOverlay.style.display = 'none';
                  
                  // 尝试多种方式获取Phaser场景
                  console.log('window.game存在:', !!window.game);
                  console.log('window.game.scene存在:', !!(window.game && window.game.scene));
                  
                  let scene = null;
                  if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                    scene = window.game.scene.getScene('MainScene');
                    console.log('通过getScene获取到场景');
                  } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                    scene = window.game.scene.keys['MainScene'];
                    console.log('通过scene.keys获取到场景');
                  } else {
                    console.log('无法获取到Phaser场景，尝试延迟重试');
                    // 延迟100ms重试
                    setTimeout(() => {
                      let retryScene = null;
                      if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                        retryScene = window.game.scene.getScene('MainScene');
                        console.log('延迟重试成功，通过getScene获取到场景');
                      } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                        retryScene = window.game.scene.keys['MainScene'];
                        console.log('延迟重试成功，通过scene.keys获取到场景');
                      } else {
                        console.log('延迟重试仍然失败，直接隐藏遮罩');
                        // 直接尝试隐藏遮罩 - 使用更直接的方式
                        console.log('尝试直接访问game对象:', window.game);
                        if (window.game) {
                          console.log('game对象存在，尝试访问scenes:', window.game.scene);
                          if (window.game.scene && window.game.scene.scenes) {
                            console.log('scenes数组存在，长度:', window.game.scene.scenes.length);
                            // 遍历所有场景找到MainScene
                            for (let i = 0; i < window.game.scene.scenes.length; i++) {
                              const scene = window.game.scene.scenes[i];
                              console.log('场景', i, ':', scene.scene.key);
                              if (scene.scene.key === 'MainScene') {
                                console.log('找到MainScene，直接隐藏遮罩');
                                if (scene.popupContainer) {
                                  scene.popupContainer.setVisible(false);
                                  scene.isPopupOpen = false;
                                  console.log('直接隐藏遮罩成功');
                                  // 尝试赠送香
                                  scene.onSharePopupClosed();
                                }
                                return;
                              }
                            }
                          }
                        }
                        console.log('所有方式都失败，无法隐藏遮罩');
                        return;
                      }
                      
                      if (retryScene) {
                        console.log('延迟重试：调用closeSharePopup');
                        retryScene.closeSharePopup();
                        console.log('延迟重试：调用onSharePopupClosed');
                        retryScene.onSharePopupClosed();
                      }
                    }, 100);
                    return;
                  }
                  
                  if (scene) {
                    console.log('调用closeSharePopup');
                    scene.closeSharePopup(); // 关闭Phaser遮罩
                    console.log('调用onSharePopupClosed');
                    scene.onSharePopupClosed(); // 赠送香
                  }
                }
              }, 5000); // 5秒
            }
          }
        });
      });

      // 开始观察弹窗元素
      const qrcodeOverlay = document.getElementById('qrcode-overlay');
      const shareOverlay = document.getElementById('share-overlay');
      
      if (qrcodeOverlay) {
        observer.observe(qrcodeOverlay, { attributes: true });
        
        // 如果二维码弹窗已经显示，立即记录时间
        if (qrcodeOverlay.style.display === 'flex') {
          recordPopupOpenTime();
        }
      }
      
      if (shareOverlay) {
        observer.observe(shareOverlay, { attributes: true });
        
        // 如果分享弹窗已经显示，立即启动定时器
        if (shareOverlay.style.display === 'flex') {
          console.log('分享弹窗显示，5秒后自动赠送香');
          setTimeout(() => {
            if (shareOverlay.style.display === 'flex') {
              console.log('5秒时间到，自动赠送香并关闭分享弹窗');
              
              // 先隐藏HTML弹窗
              shareOverlay.style.display = 'none';
              
              // 尝试多种方式获取Phaser场景
              console.log('window.game存在:', !!window.game);
              console.log('window.game.scene存在:', !!(window.game && window.game.scene));
              
              let scene = null;
              if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                scene = window.game.scene.getScene('MainScene');
                console.log('通过getScene获取到场景');
              } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                scene = window.game.scene.keys['MainScene'];
                console.log('通过scene.keys获取到场景');
              } else {
                console.log('无法获取到Phaser场景，尝试延迟重试');
                // 延迟100ms重试
                setTimeout(() => {
                  let retryScene = null;
                  if (window.game && window.game.scene && window.game.scene.getScene('MainScene')) {
                    retryScene = window.game.scene.getScene('MainScene');
                    console.log('延迟重试成功，通过getScene获取到场景');
                  } else if (window.game && window.game.scene && window.game.scene.keys && window.game.scene.keys['MainScene']) {
                    retryScene = window.game.scene.keys['MainScene'];
                    console.log('延迟重试成功，通过scene.keys获取到场景');
                  } else {
                    console.log('延迟重试仍然失败，直接隐藏遮罩');
                    // 直接尝试隐藏遮罩 - 使用更直接的方式
                    console.log('尝试直接访问game对象:', window.game);
                    if (window.game) {
                      console.log('game对象存在，尝试访问scenes:', window.game.scene);
                      if (window.game.scene && window.game.scene.scenes) {
                        console.log('scenes数组存在，长度:', window.game.scene.scenes.length);
                        // 遍历所有场景找到MainScene
                        for (let i = 0; i < window.game.scene.scenes.length; i++) {
                          const scene = window.game.scene.scenes[i];
                          console.log('场景', i, ':', scene.scene.key);
                          if (scene.scene.key === 'MainScene') {
                            console.log('找到MainScene，直接隐藏遮罩');
                            if (scene.popupContainer) {
                              scene.popupContainer.setVisible(false);
                              scene.isPopupOpen = false;
                              console.log('直接隐藏遮罩成功');
                              // 尝试赠送香
                              scene.onSharePopupClosed();
                            }
                            return;
                          }
                        }
                      }
                    }
                    console.log('所有方式都失败，无法隐藏遮罩');
                    return;
                  }
                  
                  if (retryScene) {
                    console.log('延迟重试：调用closeSharePopup');
                    retryScene.closeSharePopup();
                    console.log('延迟重试：调用onSharePopupClosed');
                    retryScene.onSharePopupClosed();
                  }
                }, 100);
                return;
              }
              
              if (scene) {
                console.log('调用closeSharePopup');
                scene.closeSharePopup();
                console.log('调用onSharePopupClosed');
                scene.onSharePopupClosed();
              }
            }
          }, 5000);
        }
      }
    })();
  </script>

  <!-- 微信 JS-SDK 分享配置，与 article.php 保持一致，仅链接改为 game.php -->
  <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
  <script>
    wx.config({
      debug: false,
      appId: '<?php echo $signPackage["appId"];?>',
      timestamp: '<?php echo $signPackage["timestamp"];?>',
      nonceStr: '<?php echo $signPackage["nonceStr"];?>',
      signature: '<?php echo $signPackage["signature"];?>',
      jsApiList: [
        'updateAppMessageShareData', // 自定义“分享给朋友”
        'updateTimelineShareData'    // 自定义“分享到朋友圈”
      ]
    });

    wx.checkJsApi({
      jsApiList: ['chooseImage'],
      success: function(res) {
        // 可用性检测结果可按需处理
      }
    });

    wx.ready(function () {
      wx.updateAppMessageShareData({
        title: '<?php echo $title; ?>',
        desc: '<?php echo implode(',', $keywords); ?>',
        link: 'https://www.langshan666.com/caishen1.0.php',
        imgUrl: 'https://www.langshan666.com/assets/images/caishen/caishen.png',
        success: function () {
          // 分享成功
        }
      });

      wx.updateTimelineShareData({
        title: '<?php echo $title; ?>',
        link: 'https://www.langshan666.com/caishen1.0.php',
        imgUrl: 'https://www.langshan666.com/assets/images/caishen/caishen.png',
        success: function () {
          // 分享成功
        }
      });
    });
  </script>
</body>
</html>