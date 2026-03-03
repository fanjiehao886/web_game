const { ccclass, property } = cc._decorator;

@ccclass
export default class LoadingScene extends cc.Component {
    @property(cc.Label)
    loadingLabel: cc.Label = null;

    @property(cc.Sprite)
    barSprite: cc.Sprite = null;

    private progress: number = 0;
    private tips: string[] = [
        '从现在开始，进入祈福',
        '你准备好了吗？',
        '香为佛使，紫气东来',
        '传说，关注狼山信息网，祈福更加灵验'
    ];
    private tipIndex: number = 0;
    private tipRound: number = 0;
    private maxTipRound: number = 3;

    onLoad() {
        this.loadResources();
        this.showTips();
    }

    showTips() {
        if (this.tipRound >= this.maxTipRound) return;

        const tipLabel = new cc.Node('tip').addComponent(cc.Label);
        tipLabel.node.parent = this.node;
        tipLabel.node.setPosition(0, 80);
        tipLabel.string = this.tips[this.tipIndex];
        tipLabel.fontSize = 24;
        tipLabel.lineHeight = 30;
        tipLabel.node.color = new cc.Color(255, 255, 255);

        const fadeIn = cc.fadeIn(0.8);
        const fadeOut = cc.fadeOut(0.8);
        const delay = cc.delayTime(0.2);

        tipLabel.node.runAction(
            cc.sequence(
                fadeIn,
                delay,
                fadeOut,
                cc.callFunc(() => {
                    this.tipIndex = (this.tipIndex + 1) % this.tips.length;
                    if (this.tipIndex === 0) {
                        this.tipRound++;
                    }
                    if (this.tipRound < this.maxTipRound) {
                        this.showTips();
                    }
                })
            )
        );
    }

    loadResources() {
        const resources = [
            { type: 'image', url: 'images/caishen/bg_caishen' },
            { type: 'image', url: 'images/caishen/altar' },
            { type: 'image', url: 'images/caishen/incense_burner' },
            { type: 'image', url: 'images/caishen/incense_unlit' },
            { type: 'image', url: 'images/caishen/incense_lit' },
            { type: 'image', url: 'images/caishen/btn_burn' },
            { type: 'image', url: 'images/caishen/popup_bg' },
            { type: 'image', url: 'images/caishen/qrcode' },
            { type: 'image', url: 'images/caishen/btn_close' },
            { type: 'image', url: 'images/caishen/coin' },
            { type: 'image', url: 'images/caishen/share_timeline' },
            { type: 'audio', url: 'audio/caishen/burn_sound' },
            { type: 'audio', url: 'audio/caishen/bgm_temple' },
            { type: 'audio', url: 'audio/caishen/drop' }
        ];

        let loadedCount = 0;
        const total = resources.length;

        resources.forEach((res) => {
            if (res.type === 'image') {
                cc.loader.loadRes(res.url, cc.SpriteFrame, (err, spriteFrame) => {
                    if (err) {
                        console.error('加载失败:', res.url, err);
                    } else {
                        console.log('加载成功:', res.url);
                    }
                    this.updateProgress(++loadedCount, total);
                });
            } else if (res.type === 'audio') {
                cc.loader.loadRes(res.url, cc.AudioClip, (err, clip) => {
                    if (err) {
                        console.error('加载失败:', res.url, err);
                    } else {
                        console.log('加载成功:', res.url);
                    }
                    this.updateProgress(++loadedCount, total);
                });
            }
        });
    }

    updateProgress(current: number, total: number) {
        this.progress = current / total;
        if (this.barSprite && this.barSprite.node) {
            this.barSprite.node.width = 300 * this.progress;
        }
        if (this.loadingLabel) {
            this.loadingLabel.string = `加载中... ${Math.round(this.progress * 100)}%`;
        }

        if (current >= total) {
            console.log('所有资源加载完成，准备切换到 MainScene');
            this.scheduleOnce(() => {
                console.log('切换到 MainScene');
                cc.director.loadScene('MainScene');
            }, 0.5);
        }
    }
}
