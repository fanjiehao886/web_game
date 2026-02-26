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
        this.initGame();
        this.setupEvents();
    }

    start() {
        this.openQrcodePopup();
    }

    initGame() {
        this.availableIncense = 0;
        this.burnClickCount = 0;
        this.isPopupOpen = false;

        // 获取节点并初始化
        const canvas = this.node.getChildByName('Canvas');
        if (canvas) {
            // 加载背景
            cc.resources.load('images/caishen/bg_caishen', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    const bgNode = new cc.Node('bg');
                    const bgSprite = bgNode.addComponent(cc.Sprite);
                    bgSprite.spriteFrame = spriteFrame;
                    bgNode.parent = canvas;
                    bgNode.setPosition(0, 0);

                    const widget = bgNode.addComponent(cc.Widget);
                    widget.isAlignTop = true;
                    widget.isAlignBottom = true;
                    widget.isAlignLeft = true;
                    widget.isAlignRight = true;
                    widget.top = 0;
                    widget.bottom = 0;
                    widget.left = 0;
                    widget.right = 0;
                }
            });

            // 加载香案
            cc.resources.load('images/caishen/altar', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    const altarNode = new cc.Node('altar');
                    const altarSprite = altarNode.addComponent(cc.Sprite);
                    altarSprite.spriteFrame = spriteFrame;
                    altarNode.parent = canvas;
                    altarNode.setPosition(0, 97);
                    altarNode.scale = 0.4;
                    altarNode.zIndex = 1;
                }
            });

            // 加载香炉
            cc.resources.load('images/caishen/incense_burner', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    const burnerNode = new cc.Node('incenseBurner');
                    const burnerSprite = burnerNode.addComponent(cc.Sprite);
                    burnerSprite.spriteFrame = spriteFrame;
                    burnerNode.parent = canvas;
                    burnerNode.setPosition(0, 89);
                    burnerNode.scale = 0.5;
                    burnerNode.zIndex = 2;
                }
            });

            // 加载香（未点燃）
            cc.resources.load('images/caishen/incense_unlit', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    const incenseNode = new cc.Node('incense');
                    const incenseSprite = incenseNode.addComponent(cc.Sprite);
                    incenseSprite.spriteFrame = spriteFrame;
                    incenseNode.parent = canvas;
                    incenseNode.setPosition(0, 85);
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
            incenseLabelNode.setPosition(0, 128);
            incenseLabelNode.zIndex = 10;
            this.incenseLabel = incenseLabel;

            // 提示标签
            const tipLabelNode = new cc.Node('tipLabel');
            const tipLabel = tipLabelNode.addComponent(cc.Label);
            tipLabel.string = '恭祝大家新年快乐，万事如意！';
            tipLabel.fontSize = 26;
            tipLabelNode.color = new cc.Color(255, 255, 255);
            tipLabelNode.parent = canvas;
            tipLabelNode.setPosition(0, 102);
            tipLabelNode.zIndex = 9;
            this.tipLabel = tipLabel;

            // 烧香按钮
            cc.resources.load('images/caishen/btn_burn', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    const burnBtnNode = new cc.Node('burnBtn');
                    const burnSprite = burnBtnNode.addComponent(cc.Sprite);
                    burnSprite.spriteFrame = spriteFrame;
                    burnBtnNode.parent = canvas;
                    burnBtnNode.setPosition(0, 115);
                    burnBtnNode.zIndex = 20;

                    const button = burnBtnNode.addComponent(cc.Button);
                    button.target = burnSprite;

                    burnBtnNode.on(cc.Node.EventType.TOUCH_END, this.handleBurnClick, this);
                    this.burnBtn = burnBtnNode;
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

            // 弹窗容器
            const popupContainer = new cc.Node('popupContainer');
            popupContainer.active = false;
            popupContainer.zIndex = 100;
            popupContainer.parent = canvas;
            this.popupContainer = popupContainer;

            // 遮罩
            const maskNode = new cc.Node('mask');
            const maskSprite = maskNode.addComponent(cc.Sprite);
            maskSprite.spriteFrame = new cc.SpriteFrame();
            maskSprite.color = new cc.Color(0, 0, 0, 153);
            maskNode.parent = popupContainer;
            maskNode.setContentSize(720, 1280);
            maskNode.setPosition(0, 0);
            maskNode.addComponent(cc.BlockInputEvents);
        }

        this.loadAudio();
    }

    loadAudio() {
        cc.resources.load('audio/caishen/bgm_temple', cc.AudioClip, (err, clip) => {
            if (!err && clip) {
                this.bgm = clip;
                cc.audioEngine.playMusic(this.bgm, true);
            }
        });
    }

    setupEvents() {
        this.createHtmlElements();
    }

    createHtmlElements() {
        if (!document.getElementById('qrcode-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'qrcode-overlay';
            overlay.style.cssText = `
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
            `;
            overlay.innerHTML = `
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
                        <img id="qrcode-img" src="../../assets/resources/images/caishen/qrcode.png" alt="二维码" style="width:60vw;max-width:300px;" />
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            document.getElementById('qrcode-close-btn').addEventListener('click', () => {
                this.closeQrcodePopup();
            });
        }

        if (!document.getElementById('share-overlay')) {
            const shareOverlay = document.createElement('div');
            shareOverlay.id = 'share-overlay';
            shareOverlay.style.cssText = `
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
            `;
            shareOverlay.innerHTML = `
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
                        <img id="share-img" src="../../assets/resources/images/caishen/share_timeline.png" alt="分享图片" style="width:60vw;max-width:300px;border-radius:8px;" />
                    </div>
                </div>
            `;
            document.body.appendChild(shareOverlay);

            document.getElementById('share-close-btn').addEventListener('click', () => {
                this.closeSharePopup();
            });
        }
    }

    handleBurnClick() {
        this.burnClickCount++;

        if (this.availableIncense > 0) {
            this.availableIncense--;
            this.updateIncenseLabel();
            this.playBurnAnimation();
        } else {
            if (this.burnClickCount === 1) {
                this.openQrcodePopup();
            } else if (this.burnClickCount === 2) {
                this.openSharePopup();
            } else {
                this.openQrcodePopup();
            }
        }
    }

    playBurnAnimation() {
        if (this.incenseSprite) {
            cc.resources.load('images/caishen/incense_lit', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    this.incenseSprite.spriteFrame = spriteFrame;
                    this.incenseSprite.node.scale = 0.2;

                    const floatUp = cc.moveBy(0.5, 0, 10);
                    const floatDown = cc.moveBy(0.5, 0, -10);
                    this.incenseSprite.node.runAction(cc.sequence(floatUp, floatDown).repeat(5));
                }
            });
        }

        cc.resources.load('audio/caishen/burn_sound', cc.AudioClip, (err, clip) => {
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

        for (let i = 0; i < coinCount; i++) {
            const coin = new cc.Node('coin');
            const sprite = coin.addComponent(cc.Sprite);

            cc.resources.load('images/caishen/coin', cc.SpriteFrame, (err, spriteFrame) => {
                if (!err && spriteFrame) {
                    sprite.spriteFrame = spriteFrame;
                }
            });

            coin.parent = this.node;
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
                    cc.resources.load('audio/caishen/drop', cc.AudioClip, (err, clip) => {
                        if (!err && clip) {
                            cc.audioEngine.playEffect(clip, false);
                        }
                    });
                }, i * 0.4);
            }
        }, 1);
    }

    openQrcodePopup() {
        if (this.isPopupOpen) return;

        this.isPopupOpen = true;
        this.popupOpenTime = Date.now();

        const overlay = document.getElementById('qrcode-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }

        if (this.popupContainer) {
            this.popupContainer.active = true;
        }
    }

    openSharePopup() {
        if (this.isPopupOpen) return;

        this.isPopupOpen = true;

        const overlay = document.getElementById('share-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }

        if (this.popupContainer) {
            this.popupContainer.active = true;
        }

        this.scheduleOnce(() => {
            const shareOverlay = document.getElementById('share-overlay');
            if (shareOverlay && shareOverlay.style.display === 'flex') {
                this.closeSharePopup();
                this.availableIncense++;
                this.updateIncenseLabel();
                this.showToast('分享成功！获得1根香！');
            }
        }, 5);
    }

    closeQrcodePopup() {
        if (!this.isPopupOpen) return;

        const displayTime = Date.now() - this.popupOpenTime;
        const shouldGiveIncense = this.burnClickCount < 3 && displayTime >= 5000;

        this.isPopupOpen = false;

        const overlay = document.getElementById('qrcode-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }

        if (this.popupContainer) {
            this.popupContainer.active = false;
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

        const overlay = document.getElementById('share-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }

        if (this.popupContainer) {
            this.popupContainer.active = false;
        }
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
