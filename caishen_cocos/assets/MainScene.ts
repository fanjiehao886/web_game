const { ccclass, property } = cc._decorator;

@ccclass
export default class MainScene extends cc.Component {
    @property(cc.Sprite)
    bg: cc.Sprite = null;

    @property(cc.Sprite)
    altar: cc.Sprite = null;

    @property(cc.Sprite)
    incenseBurner: cc.Sprite = null;

    @property(cc.Sprite)
    incenseSprite: cc.Sprite = null;

    @property(cc.Label)
    incenseLabel: cc.Label = null;

    @property(cc.Label)
    tipLabel: cc.Label = null;

    @property(cc.Node)
    burnBtn: cc.Node = null;

    @property(cc.Label)
    toastLabel: cc.Label = null;

    @property(cc.Node)
    popupContainer: cc.Node = null;

    @property(cc.Sprite)
    popupBg: cc.Sprite = null;

    @property(cc.Sprite)
    qrcodeImg: cc.Sprite = null;

    @property(cc.Node)
    closeBtn: cc.Node = null;

    private availableIncense: number = 0;
    private isPopupOpen: boolean = false;
    private burnClickCount: number = 0;
    private popupOpenTime: number = 0;
    private bgm: cc.AudioClip = null;

    onLoad() {
        console.log('MainScene onLoad');
        this.initGame();
        this.setupEvents();

        // 延迟打开二维码弹窗，确保场景初始化完成
        this.scheduleOnce(() => {
            console.log('准备打开二维码弹窗');
            this.openQrcodePopup();
        }, 1);
    }

    start() {
        console.log('MainScene start');

        // 在 Canvas 上添加点击事件监听（使用 mousedown 作为备用）
        const canvasElement = document.getElementById('game-canvas');
        if (canvasElement) {
            console.log('找到 Canvas 元素，添加 mousedown 监听');
            canvasElement.addEventListener('mousedown', (e) => {
                console.log('Canvas 元素 mousedown 事件，坐标:', e.clientX, e.clientY);
            });
            canvasElement.addEventListener('touchstart', (e) => {
                console.log('Canvas 元素 touchstart 事件，坐标:', e.touches[0].clientX, e.touches[0].clientY);
            });
        }

        // 游戏启动时打开二维码弹窗
        this.openQrcodePopup();
    }

    initGame() {
        this.availableIncense = 0;
        this.burnClickCount = 0;
        this.isPopupOpen = false;

        // 获取 Canvas 节点（this.node 的父节点）
        const canvas = this.node.parent;
        if (!canvas) {
            console.error('Canvas 节点不存在');
            return;
        }

        console.log('Canvas 节点存在，开始加载背景图');

        // 加载背景 - 使用 cc.loader.loadRes
        cc.loader.loadRes('images/caishen/bg_caishen', cc.SpriteFrame, (err, spriteFrame) => {
            if (err) {
                console.error('背景图加载失败:', err);
                return;
            }
            if (!spriteFrame) {
                console.error('背景图 SpriteFrame 为空');
                return;
            }

            console.log('背景图加载成功，开始创建节点');

            const bgNode = new cc.Node('bg');
            const bgSprite = bgNode.addComponent(cc.Sprite);
            bgSprite.spriteFrame = spriteFrame;
            bgSprite.sizeMode = cc.Sprite.SizeMode.CUSTOM; // 自定义尺寸

            bgNode.parent = canvas;
            bgNode.setPosition(0, 0);
            bgNode.zIndex = 0; // 设置背景在最底层
            bgNode.setContentSize(720, 1280); // 设置为 Canvas 大小

            const widget = bgNode.addComponent(cc.Widget);
            widget.isAlignTop = true;
            widget.isAlignBottom = true;
            widget.isAlignLeft = true;
            widget.isAlignRight = true;
            widget.top = 0;
            widget.bottom = 0;
            widget.left = 0;
            widget.right = 0;
            widget.updateAlignment();

            console.log('背景节点创建完成，位置:', bgNode.position, '大小:', bgNode.getContentSize());
        });

        // 加载香案 - 修正位置
        cc.loader.loadRes('images/caishen/altar', cc.SpriteFrame, (err, spriteFrame) => {
            if (!err && spriteFrame) {
                const altarNode = new cc.Node('altar');
                const altarSprite = altarNode.addComponent(cc.Sprite);
                altarSprite.spriteFrame = spriteFrame;
                altarNode.parent = canvas;
                altarNode.setPosition(0, 332); // 1280 * 0.76 - 640 = 332.8
                altarNode.scale = 0.4;
                altarNode.zIndex = 1;
            }
        });

        // 加载香炉 - 修正位置
        cc.loader.loadRes('images/caishen/incense_burner', cc.SpriteFrame, (err, spriteFrame) => {
            if (!err && spriteFrame) {
                const burnerNode = new cc.Node('incenseBurner');
                const burnerSprite = burnerNode.addComponent(cc.Sprite);
                burnerSprite.spriteFrame = spriteFrame;
                burnerNode.parent = canvas;
                burnerNode.setPosition(0, 256); // 1280 * 0.7 - 640 = 256
                burnerNode.scale = 0.5;
                burnerNode.zIndex = 2;
            }
        });

        // 加载香（未点燃）- 修正位置
        cc.loader.loadRes('images/caishen/incense_unlit', cc.SpriteFrame, (err, spriteFrame) => {
            if (!err && spriteFrame) {
                const incenseNode = new cc.Node('incense');
                const incenseSprite = incenseNode.addComponent(cc.Sprite);
                incenseSprite.spriteFrame = spriteFrame;
                incenseNode.parent = canvas;
                incenseNode.setPosition(0, 218); // 1280 * 0.67 - 640 = 217.6
                incenseNode.zIndex = 3;
                this.incenseSprite = incenseSprite;
            }
        });

        // 香火数量标签
        const incenseLabelNode = new cc.Node('incenseLabel');
        const incenseLabel = incenseLabelNode.addComponent(cc.Label);
        incenseLabel.string = `可用香：${this.availableIncense}`;
        incenseLabel.fontSize = 40;
        incenseLabelNode.color = new cc.Color(255, 231, 166);
        incenseLabelNode.parent = canvas;
        incenseLabelNode.setPosition(0, 512); // 1280 * 0.9 - 640 = 512
        incenseLabelNode.zIndex = 10;
        this.incenseLabel = incenseLabel;

        // 提示标签
        const tipLabelNode = new cc.Node('tipLabel');
        const tipLabel = tipLabelNode.addComponent(cc.Label);
        tipLabel.string = '恭祝大家新年快乐，万事如意！';
        tipLabel.fontSize = 26;
        tipLabelNode.color = new cc.Color(255, 255, 255);
        tipLabelNode.parent = canvas;
        tipLabelNode.setPosition(0, 435); // 1280 * 0.84 - 640 = 435.2
        tipLabelNode.zIndex = 9;
        this.tipLabel = tipLabel;

        // 烧香按钮 - 修正位置并添加正确的交互
        cc.loader.loadRes('images/caishen/btn_burn', cc.SpriteFrame, (err, spriteFrame) => {
            if (!err && spriteFrame) {
                const burnBtnNode = new cc.Node('burnBtn');
                const burnSprite = burnBtnNode.addComponent(cc.Sprite);
                burnSprite.spriteFrame = spriteFrame;
                burnBtnNode.parent = canvas;
                burnBtnNode.setPosition(0, -128); // 移到屏幕底部：1280 * 0.9 - 1280 = -128
                burnBtnNode.zIndex = 20;

                // 设置一个较大的点击区域
                burnBtnNode.setContentSize(600, 200);

                // 设置节点透明度用于调试
                burnBtnNode.opacity = 255;

                const button = burnBtnNode.addComponent(cc.Button);
                button.target = burnBtnNode;
                // 设置按钮过渡效果
                button.transition = cc.Button.Transition.SCALE;
                button.zoomScale = 0.9;

                console.log('烧香按钮位置:', burnBtnNode.position, '大小:', burnBtnNode.getContentSize());

                // 使用 Button 组件的点击事件
                button.node.on('click', this.handleBurnClick, this);

                // 添加触摸事件用于调试
                burnBtnNode.on(cc.Node.EventType.TOUCH_START, (event) => {
                    const touchPos = event.getLocation();
                    const localPos = burnBtnNode.convertToNodeSpaceAR(touchPos);
                    console.log('按钮被触摸，世界坐标:', touchPos, '本地坐标:', localPos);
                    event.stopPropagation(); // 阻止事件冒泡
                }, this);

                burnBtnNode.on(cc.Node.EventType.TOUCH_END, (event) => {
                    const touchPos = event.getLocation();
                    const localPos = burnBtnNode.convertToNodeSpaceAR(touchPos);
                    console.log('按钮触摸结束，世界坐标:', touchPos, '本地坐标:', localPos);
                }, this);

                this.burnBtn = burnBtnNode;

                console.log('烧香按钮创建完成，已添加触摸事件监听');
            }
        });

        // Toast 标签
        const toastNode = new cc.Node('toast');
        const toastLabel = toastNode.addComponent(cc.Label);
        toastLabel.string = '';
        toastLabel.fontSize = 32;
        toastNode.color = new cc.Color(255, 255, 153);
        toastNode.parent = canvas;
        toastNode.setPosition(0, 160);
        toastNode.zIndex = 30;
        toastNode.opacity = 0;
        this.toastLabel = toastLabel;

        // 创建弹窗容器（Cocos 原生弹窗）
        const popupContainer = new cc.Node('popupContainer');
        popupContainer.active = false;
        popupContainer.zIndex = 100;
        popupContainer.parent = canvas;

        // 遮罩层
        const maskNode = new cc.Node('mask');
        const maskSprite = maskNode.addComponent(cc.Sprite);
        maskSprite.spriteFrame = new cc.SpriteFrame();
        maskSprite.sizeMode = cc.Sprite.SizeMode.CUSTOM;
        maskNode.color = new cc.Color(0, 0, 0, 180);
        maskNode.parent = popupContainer;
        maskNode.setContentSize(720, 1280);
        maskNode.setPosition(0, 0);
        maskNode.addComponent(cc.BlockInputEvents);

        // 弹窗背景
        cc.loader.loadRes('images/caishen/popup_bg', cc.SpriteFrame, (err, spriteFrame) => {
            if (!err && spriteFrame) {
                const popupBgNode = new cc.Node('popupBg');
                const popupBgSprite = popupBgNode.addComponent(cc.Sprite);
                popupBgSprite.spriteFrame = spriteFrame;
                popupBgNode.parent = popupContainer;
                popupBgNode.setPosition(0, 0);
                popupBgNode.scale = 0.5; // 调整为 0.5，缩小弹窗
                popupBgNode.zIndex = 1;

                // 二维码图片容器
                const qrcodeImgNode = new cc.Node('qrcodeImg');
                const qrcodeSprite = qrcodeImgNode.addComponent(cc.Sprite);
                cc.loader.loadRes('images/caishen/qrcode', cc.SpriteFrame, (err, qrcodeSpriteFrame) => {
                    if (!err && qrcodeSpriteFrame) {
                        qrcodeSprite.spriteFrame = qrcodeSpriteFrame;
                    }
                });
                qrcodeImgNode.parent = popupBgNode;
                qrcodeImgNode.setPosition(0, 20);
                qrcodeImgNode.scale = 0.5;
                qrcodeImgNode.zIndex = 2;

                // 提示文字
                const textNode = new cc.Node('popupText');
                const textLabel = textNode.addComponent(cc.Label);
                textLabel.string = '长按识别二维码关注"狼山信息"，关注后即可获得1根香！';
                textLabel.fontSize = 18; // 缩小字体
                textLabel.lineHeight = 24;
                textLabel.overflow = cc.Label.Overflow.SHRINK;
                textNode.color = new cc.Color(255, 255, 255);
                textNode.width = 300;
                textNode.parent = popupBgNode;
                textNode.setPosition(0, 160);
                textNode.zIndex = 3;

                // 关闭按钮
                cc.loader.loadRes('images/caishen/btn_close', cc.SpriteFrame, (err, closeSpriteFrame) => {
                    if (!err && closeSpriteFrame) {
                        const closeBtnNode = new cc.Node('closeBtn');
                        const closeSprite = closeBtnNode.addComponent(cc.Sprite);
                        closeSprite.spriteFrame = closeSpriteFrame;
                        closeBtnNode.parent = popupBgNode;
                        closeBtnNode.setPosition(140, 140);
                        closeBtnNode.scale = 0.6;
                        closeBtnNode.zIndex = 4;

                        const closeBtn = closeBtnNode.addComponent(cc.Button);
                        closeBtn.target = closeBtnNode;
                        closeBtn.transition = cc.Button.Transition.SCALE;
                        closeBtn.zoomScale = 0.9;
                        closeBtn.node.on('click', () => {
                            this.closeQrcodePopup();
                        }, this);
                    }
                });
            }
        });

        this.popupContainer = popupContainer;

        this.loadAudio();
    }

    loadAudio() {
        cc.loader.loadRes('audio/caishen/bgm_temple', cc.AudioClip, (err, clip) => {
            if (!err && clip) {
                this.bgm = clip;
                cc.audioEngine.playMusic(this.bgm, true);
            }
        });
    }

    setupEvents() {
        // HTML 弹窗已经由 index.html 创建，直接调用全局函数
    }

    openQrcodePopup() {
        console.log('openQrcodePopup 被调用，当前 isPopupOpen:', this.isPopupOpen);
        if (this.isPopupOpen) return;

        this.isPopupOpen = true;
        this.popupOpenTime = Date.now();

        if (this.popupContainer) {
            this.popupContainer.active = true;
            console.log('二维码弹窗已显示');
        } else {
            console.error('popupContainer 不存在');
        }
    }

    openSharePopup() {
        if (this.isPopupOpen) return;

        this.isPopupOpen = true;

        if (this.popupContainer) {
            this.popupContainer.active = true;
            console.log('分享弹窗已显示');
        }
    }

    closeQrcodePopup() {
        if (!this.isPopupOpen) return;

        const displayTime = Date.now() - this.popupOpenTime;
        const shouldGiveIncense = this.burnClickCount < 3 && displayTime >= 5000;

        this.isPopupOpen = false;

        if (this.popupContainer) {
            this.popupContainer.active = false;
            console.log('二维码弹窗已关闭');
        }

        if (shouldGiveIncense) {
            this.availableIncense++;
            this.updateIncenseLabel();
            this.showToast('获得1根香！');
        } else {
            this.showToast('恳求关注公众号，获取更多香火');
        }
    }

    closeSharePopup() {
        this.isPopupOpen = false;

        if (this.popupContainer) {
            this.popupContainer.active = false;
            console.log('分享弹窗已关闭');
        }
    }

    handleBurnClick() {
        console.log('handleBurnClick 被调用，当前点击次数:', this.burnClickCount);
        this.burnClickCount++;

        if (this.availableIncense > 0) {
            this.availableIncense--;
            this.updateIncenseLabel();
            this.playBurnAnimation();
        } else {
            if (this.burnClickCount === 1) {
                console.log('第一次点击，打开二维码弹窗');
                this.openQrcodePopup();
            } else if (this.burnClickCount === 2) {
                console.log('第二次点击，打开分享弹窗');
                this.openSharePopup();
            } else {
                console.log('第三次或更多次点击，打开二维码弹窗');
                this.openQrcodePopup();
            }
        }
    }

    playBurnAnimation() {
        if (this.incenseSprite) {
            cc.loader.loadRes('images/caishen/incense_lit', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    this.incenseSprite.spriteFrame = spriteFrame;
                    this.incenseSprite.node.scale = 0.2;

                    const floatUp = cc.moveBy(0.5, 0, 10);
                    const floatDown = cc.moveBy(0.5, 0, -10);
                    this.incenseSprite.node.runAction(cc.sequence(floatUp, floatDown).repeat(5));
                }
            });
        }

        cc.loader.loadRes('audio/caishen/burn_sound', cc.AudioClip, (err, clip) => {
            if (!err && clip) {
                const audioId = cc.audioEngine.playEffect(clip, false);
                this.scheduleOnce(() => {
                    cc.audioEngine.stopEffect(audioId);
                }, 0.3);
            }
        });

        this.createCoinRain();
    }

    createCoinRain() {
        const coinCount = 20;
        const visibleSize = cc.view.getVisibleSize();
        const canvas = this.node.parent || this.node;

        for (let i = 0; i < coinCount; i++) {
            const coin = new cc.Node('coin');
            const sprite = coin.addComponent(cc.Sprite);

            cc.loader.loadRes('images/caishen/coin', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    sprite.spriteFrame = spriteFrame;
                }
            });

            coin.parent = canvas;
            const startX = Math.random() * visibleSize.width - visibleSize.width / 2;
            const startY = visibleSize.height / 2 + 200 + Math.random() * 200;
            coin.setPosition(startX, startY);
            coin.scale = 0.2 + Math.random() * 0.3;

            const endY = -visibleSize.height * 0.25 + Math.random() * 200;
            const duration = 3 + Math.random() * 0.6;

            coin.runAction(
                cc.sequence(
                    cc.moveTo(duration, cc.v2(startX, endY)),
                    cc.callFunc(() => {
                        coin.destroy();
                    })
                )
            );

            coin.runAction(cc.rotateBy(duration, 360));
        }

        this.scheduleOnce(() => {
            for (let i = 0; i < 3; i++) {
                this.scheduleOnce(() => {
                    cc.loader.loadRes('audio/caishen/drop', cc.AudioClip, (err, clip) => {
                        if (!err && clip) {
                            cc.audioEngine.playEffect(clip, false);
                        }
                    });
                }, i * 0.4);
            }
        }, 1);
    }

    showToast(message: string) {
        if (!this.toastLabel) return;

        this.toastLabel.string = message;
        this.toastLabel.node.opacity = 255;

        this.toastLabel.node.stopAllActions();
        this.toastLabel.node.runAction(
            cc.sequence(
                cc.delayTime(1),
                cc.fadeOut(2)
            )
        );
    }

    updateIncenseLabel() {
        if (this.incenseLabel) {
            this.incenseLabel.string = `可用香：${this.availableIncense}`;
        }
    }

    onDestroy() {
        const qrcodeOverlay = document.getElementById('qrcode-overlay');
        const shareOverlay = document.getElementById('share-overlay');
        if (qrcodeOverlay) qrcodeOverlay.remove();
        if (shareOverlay) shareOverlay.remove();
    }
}
