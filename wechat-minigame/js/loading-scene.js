// 加载场景
class LoadingScene extends Phaser.Scene {
  constructor() {
    super('LoadingScene')
  }

  preload() {
    const width = this.cameras.main.width
    const height = this.cameras.main.height

    const progressBox = this.add.rectangle(width / 2, height / 2, 320, 50, 0x222222, 0.8)
    const progressBar = this.add.rectangle(width / 2 - 150, height / 2, 0, 30, 0xffd700, 1).setOrigin(0, 0.5)
    const loadingText = this.add.text(width / 2, height / 2 - 50, '加载中...', {
      fontFamily: 'Arial',
      fontSize: '28px',
      color: '#ffffff'
    }).setOrigin(0.5, 0.5)
    const percentText = this.add.text(width / 2, height / 2, '0%', {
      fontFamily: 'Arial',
      fontSize: '24px',
      color: '#ffffff'
    }).setOrigin(0.5, 0.5)

    // 渐显提示文字：在加载过程中循环显示四句话
    const tips = [
      '从现在开始，进入祈福',
      '你准备好了吗？',
      '香为佛使，紫气东来',
      '传说，关注狼山信息网，祈福更加灵验'
    ]
    let tipIndex = 0
    // 轮播控制：tipRound 表示当前已经播放了多少轮，maxTipRound 表示最多播放多少轮
    this.tipRound = 0
    this.maxTipRound = 3 // 如需多轮，改成 2 或 3
    const tipText = this.add.text(width / 2, height / 2 + 80, tips[0], {
      fontFamily: 'Arial',
      fontSize: '24px',
      color: '#ffffff',
      align: 'center',
      wordWrap: { width: 360 }
    }).setOrigin(0.5, 0.5)
    tipText.setAlpha(0)

    const playNextTip = () => {
      const currentTip = tips[tipIndex]
      tipText.setText(currentTip)
      tipText.setAlpha(0)

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
                tipIndex = (tipIndex + 1) % tips.length

                // 每回到第一句，说明播完一整轮
                if (tipIndex === 0) {
                  this.tipRound++
                }

                // 只要还没播完设定的轮数，就继续播下一句
                if (this.tipRound < this.maxTipRound) {
                  this.time.delayedCall(200, playNextTip)
                }
              }
            })
          })
        }
      })
    }

    playNextTip()

    // 在加载场景底部显示"巧果网络出品"
    const producerText = this.add.text(width / 2, height - 60, '巧果网络出品', {
      fontFamily: 'Arial',
      fontSize: '20px',
      color: '#cccccc'
    }).setOrigin(0.5, 0.5)
    // 在其下方显示网址
    const urlText = this.add.text(width / 2, height - 30, 'qiaoguokeji.com', {
      fontFamily: 'Arial',
      fontSize: '18px',
      color: '#999999'
    }).setOrigin(0.5, 0.5)

    this.load.on('progress', (value) => {
      progressBar.width = 300 * value
      percentText.setText(Math.round(value * 100) + '%')
    })

    this.load.on('complete', () => {
      this.time.delayedCall(200, () => {
        this.scene.start('MainScene')
      })
    })

    // 在这里加载所有游戏资源
    this.load.image('bg_caishen', 'images/bg_caishen.png')
    this.load.image('altar', 'images/altar.png')
    this.load.image('incense_burner', 'images/incense_burner.png')
    this.load.image('incense_unlit', 'images/incense_unlit.png')
    this.load.image('incense_lit', 'images/incense_lit.png')
    this.load.image('btn_burn', 'images/btn_burn.png')
    this.load.image('popup_bg', 'images/popup_bg.png')
    this.load.image('qrcode', 'images/qrcode.png')
    this.load.image('btn_close', 'images/btn_close.png')
    this.load.image('coin', 'images/coin.png')
    this.load.image('share_timeline', 'images/share_timeline.png')
    this.load.image('caishen', 'images/caishen.png')

    this.load.audio('burn_sound', ['audio/burn_sound.wav'])
    this.load.audio('bgm_temple', ['audio/bgm_temple.mp3'])
    this.load.audio('coin_drop', ['audio/drop.mp3'])
  }
}