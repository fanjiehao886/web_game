// 财神烧香 - 微信小游戏版本
// 入口文件

// 引入适配器
import './js/adapter'

// 引入游戏场景
import './js/loading-scene.js'
import './js/main-scene.js'

// 游戏配置
const GAME_WIDTH = 720
const GAME_HEIGHT = 1280

// 游戏初始化
wx.onLaunch(() => {
  console.log('游戏启动')
  
  // 获取系统信息
  const systemInfo = wx.getSystemInfoSync()
  console.log('系统信息:', systemInfo)
  
  // 初始化游戏
  initGame()
})

function initGame() {
  // Phaser游戏配置
  const config = {
    type: Phaser.AUTO,
    width: GAME_WIDTH,
    height: GAME_HEIGHT,
    parent: 'game-container',
    physics: {
      default: 'arcade',
      arcade: {
        gravity: { y: 0 }
      }
    },
    scale: {
      mode: Phaser.Scale.ENVELOP,
      autoCenter: Phaser.Scale.CENTER_BOTH
    },
    scene: [LoadingScene, MainScene]
  }

  // 创建游戏实例
  const game = new Phaser.Game(config)
  
  // 全局游戏实例
  window.game = game
}