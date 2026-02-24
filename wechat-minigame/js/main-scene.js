// 主游戏场景
class MainScene extends Phaser.Scene {
  constructor() {
    super('MainScene')
    this.availableIncense = 3 // 初始香的数量
    this.burnClickCount = 0 // 记录烧香点击次数
    this.isPopupOpen = false // 记录弹窗是否打开
    this.incenseLit = false // 记录香是否已点燃
  }

  create() {
    // 添加背景
    this.add.image(GAME_WIDTH / 2, GAME_HEIGHT / 2, 'bg_caishen')

    // 添加祭坛
    this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.6, 'altar')

    // 添加香炉
    this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.5, 'incense_burner')

    // 添加未点燃的香
    this.incense = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.45, 'incense_unlit')

    // 添加烧香按钮
    this.btnBurn = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.75, 'btn_burn')
      .setInteractive()
      .on('pointerdown', this.onBurnClick, this)

    // 添加可用香数量显示
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
    ).setOrigin(0.5, 0.5)

    // 添加祝福语
    this.add.text(
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
    ).setOrigin(0.5, 0.5)

    // 添加烧香提示文字
    this.burnTipText = this.add.text(
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
    ).setOrigin(0.5, 0.5)

    // 创建 Phaser 内部的半透明遮罩，仅用于暗背景（不放真实二维码逻辑）
    this.popupContainer = this.add.container()
    this.popupMask = this.add.rectangle(GAME_WIDTH / 2, GAME_HEIGHT / 2, GAME_WIDTH, GAME_HEIGHT, 0x000000, 0.7)
    this.popupContainer.add(this.popupMask)
    this.popupContainer.setVisible(false)
    this.popupContainer.setDepth(100)

    // 初始化微信登录
    this.initWxLogin()
  }

  initWxLogin() {
    // 微信小游戏登录
    if (typeof wx !== 'undefined') {
      wx.login({
        success: (res) => {
          console.log('登录成功', res.code)
          // 这里可以将code发送到服务器换取openid和session_key
        },
        fail: (err) => {
          console.error('登录失败', err)
        }
      })

      // 获取用户信息
      wx.getUserInfo({
        success: (res) => {
          console.log('获取用户信息成功', res.userInfo)
        },
        fail: (err) => {
          console.error('获取用户信息失败', err)
        }
      })
    }
  }

  onBurnClick() {
    if (this.availableIncense <= 0) {
      this.showNoIncenseTip()
      return
    }

    if (this.incenseLit) {
      return // 香已经点燃，不能重复点击
    }

    // 消耗一根香
    this.availableIncense--
    this.incenseText.setText('可用香：' + this.availableIncense)
    
    // 点燃香
    this.incenseLit = true
    this.incense.setTexture('incense_lit')
    
    // 播放烧香音效
    this.sound.play('burn_sound')
    
    // 增加点击次数
    this.burnClickCount++
    
    // 显示烧香成功提示
    this.showBurnSuccessTip()
    
    // 创建烟雾效果
    this.createSmokeEffect()
    
    // 3秒后显示分享弹窗或二维码弹窗
    this.time.delayedCall(3000, () => {
      // 根据点击次数决定显示哪个弹窗
      if (this.burnClickCount % 3 === 0) {
        // 每3次显示分享弹窗
        this.showSharePopup()
      } else {
        // 其他情况显示二维码弹窗
        this.showQrcodePopup()
      }
    })
  }

  showBurnSuccessTip() {
    const tips = [
      '心诚则灵，福报自来',
      '香火鼎盛，财源广进',
      '善心善行，菩萨保佑',
      '烧香拜佛，心想事成'
    ]
    const randomTip = tips[Math.floor(Math.random() * tips.length)]
    this.burnTipText.setText(randomTip)
    
    // 淡入效果
    this.tweens.add({
      targets: this.burnTipText,
      alpha: { from: 0, to: 1 },
      duration: 800,
      onComplete: () => {
        // 3秒后淡出
        this.time.delayedCall(3000, () => {
          this.tweens.add({
            targets: this.burnTipText,
            alpha: { from: 1, to: 0 },
            duration: 800,
            onComplete: () => {
              this.burnTipText.setText('')
            }
          })
        })
      }
    })
  }

  createSmokeEffect() {
    // 创建烟雾粒子效果
    const particles = this.add.particles('incense_lit')
    
    particles.createEmitter({
      x: GAME_WIDTH / 2,
      y: GAME_HEIGHT * 0.4,
      speed: { min: 20, max: 50 },
      angle: { min: 240, max: 300 },
      scale: { start: 0.2, end: 0.8 },
      alpha: { start: 0.8, end: 0 },
      lifespan: 2000,
      quantity: 3
    })
    
    // 5秒后停止烟雾效果
    this.time.delayedCall(5000, () => {
      particles.destroy()
    })
  }

  showNoIncenseTip() {
    this.burnTipText.setText('香火已用完，分享可获得更多香火！')
    
    // 淡入效果
    this.tweens.add({
      targets: this.burnTipText,
      alpha: { from: 0, to: 1 },
      duration: 800,
      onComplete: () => {
        // 3秒后淡出
        this.time.delayedCall(3000, () => {
          this.tweens.add({
            targets: this.burnTipText,
            alpha: { from: 1, to: 0 },
            duration: 800,
            onComplete: () => {
              this.burnTipText.setText('')
            }
          })
        })
      }
    })
  }

  showSharePopup() {
    if (this.isPopupOpen) return
    
    this.isPopupOpen = true
    this.popupContainer.setVisible(true)
    
    // 添加分享背景
    const popupBg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT / 2, 'popup_bg')
    this.popupContainer.add(popupBg)
    
    // 添加分享图片
    const shareImg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.45, 'share_timeline')
      .setDisplaySize(GAME_WIDTH * 0.6, GAME_WIDTH * 0.6 * 1.5)
    this.popupContainer.add(shareImg)
    
    // 添加分享文字
    const shareText = this.add.text(
      GAME_WIDTH / 2,
      GAME_HEIGHT * 0.75,
      '分享给朋友，获得更多香火！',
      {
        fontFamily: 'Arial',
        fontSize: '24px',
        color: '#ffffff',
        align: 'center'
      }
    ).setOrigin(0.5, 0.5)
    this.popupContainer.add(shareText)
    
    // 添加关闭按钮
    const closeBtn = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.85, 'btn_close')
      .setInteractive()
      .on('pointerdown', this.closeSharePopup, this)
    this.popupContainer.add(closeBtn)
    
    // 调用微信分享API
    this.triggerWxShare()
  }

  showQrcodePopup() {
    if (this.isPopupOpen) return
    
    this.isPopupOpen = true
    this.popupContainer.setVisible(true)
    
    // 添加二维码背景
    const popupBg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT / 2, 'popup_bg')
    this.popupContainer.add(popupBg)
    
    // 添加二维码图片
    const qrcodeImg = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.45, 'qrcode')
      .setDisplaySize(GAME_WIDTH * 0.6, GAME_WIDTH * 0.6)
    this.popupContainer.add(qrcodeImg)
    
    // 添加二维码文字
    const qrcodeText = this.add.text(
      GAME_WIDTH / 2,
      GAME_HEIGHT * 0.75,
      '长按识别二维码关注"狼山信息"，\n关注后即可获得1根香！',
      {
        fontFamily: 'Arial',
        fontSize: '24px',
        color: '#ffffff',
        align: 'center'
      }
    ).setOrigin(0.5, 0.5)
    this.popupContainer.add(qrcodeText)
    
    // 添加关闭按钮
    const closeBtn = this.add.image(GAME_WIDTH / 2, GAME_HEIGHT * 0.85, 'btn_close')
      .setInteractive()
      .on('pointerdown', this.closeQrcodePopup, this)
    this.popupContainer.add(closeBtn)
    
    // 记录弹窗打开时间
    this.qrcodePopupOpenTime = Date.now()
  }

  closeSharePopup() {
    if (!this.isPopupOpen) return
    
    this.isPopupOpen = false
    this.popupContainer.setVisible(false)
    
    // 清除弹窗内容
    while (this.popupContainer.list.length > 1) {
      this.popupContainer.remove(this.popupContainer.list[this.popupContainer.list.length - 1], true)
    }
    
    // 赠送香
    this.giveIncense(2)
  }

  closeQrcodePopup() {
    if (!this.isPopupOpen) return
    
    this.isPopupOpen = false
    this.popupContainer.setVisible(false)
    
    // 清除弹窗内容
    while (this.popupContainer.list.length > 1) {
      this.popupContainer.remove(this.popupContainer.list[this.popupContainer.list.length - 1], true)
    }
    
    // 检查弹窗显示时间，只有显示超过5秒才赠送香
    const displayTime = Date.now() - this.qrcodePopupOpenTime
    if (displayTime >= 5000 && this.burnClickCount < 3) {
      this.giveIncense(1)
    }
  }

  giveIncense(count) {
    this.availableIncense += count
    this.incenseText.setText('可用香：' + this.availableIncense)
    
    // 显示获得香火的提示
    this.burnTipText.setText('获得' + count + '根香火！')
    
    // 淡入效果
    this.tweens.add({
      targets: this.burnTipText,
      alpha: { from: 0, to: 1 },
      duration: 800,
      onComplete: () => {
        // 3秒后淡出
        this.time.delayedCall(3000, () => {
          this.tweens.add({
            targets: this.burnTipText,
            alpha: { from: 1, to: 0 },
            duration: 800,
            onComplete: () => {
              this.burnTipText.setText('')
            }
          })
        })
      }
    })
  }

  triggerWxShare() {
    if (typeof wx !== 'undefined') {
      // 微信小游戏分享
      wx.shareAppMessage({
        title: '在线烧香 - 财神保佑',
        path: '/pages/index/index',
        imageUrl: 'images/caishen.png',
        success: () => {
          console.log('分享成功')
          // 分享成功后赠送香
          this.giveIncense(2)
        },
        fail: (err) => {
          console.error('分享失败', err)
        }
      })
    }
  }
}