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
    <div class="trca-docs-header-menu" @click="showHeaderMenu">
        <div class="trca-docs-header-menu-icon">
            <div class="material-icons title">
                more_vert
            </div>
        </div>
        <ul :id="id">
            <slot></slot>
        </ul>
    </div>`,
    methods: {
        showHeaderMenu: function() {
            $("ul[id^='trca-docs-share-menu-by-user-']").hide();
            $("#" + this.id).toggle();
            $(document).on('click', function (e) {
                if (!$(e.target).closest(".title").length) {
                    $("#trca-docs-header-menu-by-user").hide();
                    $("#trca-docs-header-menu-by-order").hide();
                }
                e.stopPropagation();
            });
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
    <div class="trca-docs-doc-menu" :title="title" @click="showDocMenu">
        <div class="trca-docs-doc-menu-icon">
            <div class="material-icons title">
                {{ icon }}
            </div>
        </div>
        <ul :id="id">
            <slot></slot>
        </ul>
    </div>`,
    methods: {
        showDocMenu: function() {
            $("ul[id^='trca-docs-share-menu-by-user-']").hide();
            $("#trca-docs-header-menu-by-user").hide();
            $("#trca-docs-header-menu-by-order").hide();
            $("#" + this.id).toggle();
             $(document).on('click', function (e) {
                if (!$(e.target).closest(".title").length){
                    $("ul[id^='trca-docs-share-menu-by-user-']").hide();
                }
                e.stopPropagation();
            });
        }
    }
})

Vue.component("doc-menu-button", {
    props: {
        message: String,
        icon: String,
        id: Number
    },
    template: `
    <div class="trca-docs-header-button" @click="buttonClick">
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

Vue.component("header-menu-button", {
    props: {
        message: String,
        icon: String,
        id: Array
    },
    template: `
    <div class="trca-docs-header-button" @click="buttonClick">
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
        icon: String,
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

Vue.component ("doc-info-button", {
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
    methods : {
        buttonClick: function() {
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
        maxSize: Number,
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
        buttonClick: function(event, maxSize) {
            file = event.target.files[0];
            let onFailure = () => { $('#trca-docs-footer-input').val(null) };
            let onSuccess = () => { $('#trca-docs-footer-upload').submit() };
            trustedCA.checkFileSize(file, maxSize, () => { trustedCA.checkAccessFile(file, onSuccess , onFailure ) }, onFailure );
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
