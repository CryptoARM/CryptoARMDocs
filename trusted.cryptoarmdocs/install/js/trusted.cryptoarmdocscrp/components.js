Vue.component ("trca-docs", {
    template: `
    <main class="trca-docs">
        <slot></slot>
    </main>`
})

Vue.component ("header-title", {
    props: {
        title: String
    },
    template: `
    <div class="trca-docs-header">
        {{ title }}
        <slot></slot>
    </div>`
})

Vue.component ("header-menu", {
    props: {
        id: String
    },
    template:`
    <div class="trca-docs-header-menu">
        <div class="trca-docs-header-menu-icon">
            <div class="material-icons" @click="showHeaderMenu">
                more_vert
                <ul class="trca-docs-header-menu ul" :id="id" style = "display: none;">
                    <slot></slot>
                </ul>
            </div>
        </div>
    </div>`,
    methods: {
        showHeaderMenu: function(e) {
            e.stopPropagation()
            const el = e.target.children[0]
            const disp = el.style.display
            hideAll()
            if (el.className === "trca-docs-header-menu ul") {
                el.style.display = disp === "none" ? "block" : "none"
            }
        }
    }
})

Vue.component("header-menu-button", {
    props: {
        message: String,
        icon: String,
        id: Array
    },
    template: `
    <div class="trca-docs-header-button" :title="message" @click="buttonClick">
        <div class="material-icons">{{ icon }}</div>
        {{ message }}
    </div>`,
    methods: {
        buttonClick: function() {
            this.$emit("button-click", this.id);
        }
    }
})

Vue.component ("docs-content", {
    template: `
    <div class="trca-docs-content">
        <slot></slot>
    </div>`
})

Vue.component ("docs-items", {
    template: `
    <div class="trca-docs-content-items">
        <slot></slot>
    </div>
    `
})

Vue.component ("doc-name", {
    props:{
        name: String,
        description: String,
        icon: String,
        color: String,
    },
    template: `
    <div class="trca-docs-content-item-left">
        <div class="material-icons" :style="color">
            {{ icon }}
        </div>
        <div class="trca-docs-content-doc">
            <div class="trca-docs-content-doc-name" :title="name">
                {{ name }}
            </div>
            <div class="trca-docs-content-doc-description" v-html="description">
            </div>
            <slot></slot>
        </div>
    </div>`,
})

Vue.component ("doc-name-owner", {
    props: {
        owner: String
    },
    template: `
    <div class="trca-docs-content-doc-description">
        {{ owner }}
    </div>`
})

Vue.component ("doc-buttons", {
    template: `
    <div class="trca-docs-content-item-right">
        <slot></slot>
    </div>`
})

Vue.component ("doc-button", {
    props: {
        id: Number,
        title: String,
        icon: String
    },
    template: `
    <div class="trca-docs-content-button" :title="title" @click="buttonClick">
        <i class="material-icons">
            {{ icon }}
        </i>
    </div>`,
    methods : {
        buttonClick: function() {
            let idAr = new Array();
            idAr = [this.id];
            this.$emit('button-click', idAr);
        }
    }
})

Vue.component("doc-info-button", {
    props: {
        id: Number,
        title: String,
        icon: String,
        docname: String
    },
    template: `
    <div class="trca-docs-content-button" :title="title" @click="buttonClick">
        <i class="material-icons">
            {{ icon }}
        </i>
    </div>`,
    methods: {
        buttonClick: function () {
            let idAr = new Array();
            idAr = [this.id];
            this.$emit('button-click', idAr, this.docname);
            $(document).on('click', function (e) {
                if ($(e.target).closest(".trca-modal-overlay").length) {
                    $('#trca-modal-info-window').hide();
                    $('#trca-modal-overlay').hide();
                }
            });
        }
    }
})

Vue.component ("docs-upload-file", {
    props: {
        maxsize: String,
        title: String
    },
    template: `
    <div class="trca-docs-footer">
        <form enctype="multipart/form-data" method="POST" id="trca-docs-footer-upload">
            <div class="trca-docs-footer-upload-button">
                <input id="trca-docs-footer-upload-input" class="trca-docs-footer-upload-input"
                       name="tr_ca_upload_comp_by_user" type="file" @change="buttonClick">
                {{ title }}
            </div>
        </form>
    </div>`,
    methods: {
        buttonClick: function(event) {
            file = event.target.files[0];
            let onFailure = () => { $('#trca-docs-footer-input').val(null) };
            let onSuccess = () => { $('#trca-docs-footer-upload').submit() };
            trustedCA.checkFileSize(file, this.maxsize, () => { trustedCA.checkAccessFile(file, onSuccess , onFailure ) }, onFailure );
        }
    }
})

Vue.component ("doc-menu", {
    props: {
        title: String,
        id: String,
        icon: String,
    },
    template:`
    <div class="trca-docs-doc-menu">
        <div class="trca-docs-doc-menu-icon title">
            <div class="material-icons" :title="title" @click="showDocMenu">
                {{ icon }}
                <ul class="trca-docs-doc-menu ul" :id="id" style = "display: none;">
                    <slot></slot>
                </ul>
            </div>
        </div>
    </div>`,
    methods: {
        showDocMenu: function (e)  {
            e.stopPropagation()
            const el = e.target.children[0]
            const disp = el.style.display
            hideAll()
            if (el.className === "trca-docs-doc-menu ul") {
                el.style.display = disp === "none" ? "block" : "none"
            }
        }
    }
})

Vue.component("doc-menu-button", {
    props: {
        message: String,
        icon: String,
        id: Number,
    },
    template: `
    <div class="trca-docs-menu-button" :title="message" @click="buttonClick">
        <div class="material-icons">{{ icon }}</div>
        {{ message }}
    </div>`,
    methods: {
        buttonClick: function() {
            let idAr = new Array();
            idAr = [this.id];
            this.$emit('button-click', idAr);
        }
    }
})

Vue.component ("doc-info", {
    props:{
        info: String,
        title: String
    },
    template: `
        <div class="trca-docs-content-info" :title="title">
            {{ info }}
        </div>`,
})

const hideAll = () =>
document.querySelectorAll(".trca-docs-header-menu ul, .trca-docs-doc-menu ul").forEach(
    el => el.style.display ="none"
)
document.addEventListener("click", hideAll);

