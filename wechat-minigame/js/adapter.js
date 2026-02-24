// 微信小游戏适配器
// 用于适配Web环境到微信小游戏环境

// 模拟window对象
window = window || {}

// 模拟document对象
document = document || {}

// 模拟navigator对象
navigator = navigator || {}

// 模拟localStorage
if (typeof wx !== 'undefined') {
  window.localStorage = {
    getItem: function(key) {
      return wx.getStorageSync(key) || null
    },
    setItem: function(key, value) {
      wx.setStorageSync(key, value)
    },
    removeItem: function(key) {
      wx.removeStorageSync(key)
    },
    clear: function() {
      wx.clearStorageSync()
    }
  }
  
  // 模拟XMLHttpRequest
  window.XMLHttpRequest = function() {
    return {
      open: function(method, url) {
        this.method = method
        this.url = url
      },
      send: function(data) {
        const self = this
        wx.request({
          url: this.url,
          method: this.method,
          data: data,
          success: function(res) {
            if (self.onload) {
              self.onload(res)
            }
          },
          fail: function(err) {
            if (self.onerror) {
              self.onerror(err)
            }
          }
        })
      },
      setRequestHeader: function() {},
      addEventListener: function() {}
    }
  }
  
  // 模拟Image对象
  window.Image = function() {
    const image = wx.createImage()
    return image
  }
  
  // 模拟Audio对象
  window.Audio = function(src) {
    return {
      src: src,
      play: function() {
        if (src) {
          wx.createInnerAudioContext().play()
        }
      },
      pause: function() {},
      load: function() {}
    }
  }
}