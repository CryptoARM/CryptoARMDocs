Vue.component ("trca-docs", {
    template: `
    <main class="trca-docs">
        <slot></slot>
    </main>`,
})

Vue.component ("header-title", {
    props: {
        title: String
    },
    template: `
    <div class="trca-docs-header">
        <div class="trca-docs-header-title">
            {{ title }}
        </div>
        <slot></slot>
    </div>`,
})

Vue.component ("header-menu", {
    props: {
        id: String
    },
    template:`
    <div class="trca-docs-header-menu">
        <div class="trca-docs-header-menu-icon">
            <div class="material-icons" @click="showHeaderMenu" data-id="data-menu">
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
        id: Array,
        zipname: String
    },
    template: `
    <div class="trca-docs-header-button" :title="message" @click="buttonClick">
        <div class="material-icons">{{ icon }}</div>
        {{ message }}
    </div>`,
    methods: {
        buttonClick: function() {
            this.$emit("button-click", this.id, this.zipname);
        }
    }
})

Vue.component ("docs-content", {
    template: `
    <div class="trca-docs-content">
        <slot></slot>
    </div>`,
})

Vue.component ("docs-header", {
    props: {
        title: String,
        date: String,
        id: String,
    },
    template: `
    <div class="trca-docs-content-items trca-docs-header-line">
        <input type="checkbox" id="checking_all" v-on:change="checkAll">
        <div class="trca-docs-content-item-left">
            <div class="doc-list-icon-inv">
                <div class="material-icons" style="opacity: 0;">
                    insert_drive_file
                </div>
            </div>
            <div class="trca-docs-content-doc">
                <div class="trca-docs-content-doc-name" :title="title">
                 {{ title }}
                </div>
                <slot></slot>
            </div>
        </div>
        <div class="trca-docs-content-info" :title="date">
            {{ date }}
        </div>
        <div class="trca-docs-content-info" :title="id">
            {{ id }}
        </div>
        <div class="trca-docs-content-item-right">
            <slot></slot>
        </div>
    </div>`,
    methods: {
        checkAll: function() {
            if ($('input[id="checking_all"]').prop("checked")) {
                $('input[id^="check_"]').each(function(){
                    $(this).attr("checked","checked");
                })
            } else {
                    $('input[id^="check_"]').each(function(){
                    $(this).removeAttr("checked");
                })
            }
        }
    }
})

Vue.component ("docs-items", {
    props: {
        id: String,
        title: String,
        docname: String,
        sharedstatus: Array,
        currentuseraccess: String,
        sendsome: String
    },
    template: `
    <div id="docs" class="trca-docs-content-items" :title="title" @dblclick="buttonClick" data-id="data-item">
        <input :id="id" type="checkbox" v-on:change="checking" v-if="sendsome==1">
        <slot></slot>
    </div>`,
    methods: {
        buttonClick: function () {
            let idAr = new Array();
            idAr = [this.id.replace("check_", "")];
            this.$emit('button-click', idAr, this.docname, this.sharedstatus, this.currentuseraccess);
            $(document).on('click', function (e) {
                if ($(e.target).closest(".trca-modal-overlay").length) {
                    $('#trca-modal-info-window').hide();
                    $('#trca-modal-overlay').hide();
                }
            });
        },
        checking: function() {
            let checkedAll = true;
            $('input[id^="check_"]').each(function(){
                if (!$(this).prop("checked")) {
                    $('input[id="checking_all"]').removeAttr("checked");
                    checkedAll = false;
                };
            })
            if (checkedAll) {
                $('input[id="checking_all"]').attr("checked","checked");
            }
        }
    }
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
        <div class="doc-list-icon">
            <div class="material-icons" :style="color">
                insert_drive_file
            </div>
            <div class="material-icons addition" :style="color">
                {{ icon }}
            </div>
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
    props: {
        component: String
    },
    template: `
    <div v-if="component === 'by_user'" class="trca-docs-content-item-right">
        <slot></slot>
    </div>
    <div v-else-if="component === 'by_order'" class="trca-docs-content-item-order-right">
        <slot></slot>
    </div>
    <div v-else-if="component === 'form'" class="trca-docs-content-item-form-right">
        <slot></slot>
    </div>`
})

Vue.component ("doc-button", {
    props: {
        id: Number,
        title: String,
        icon: String,
        role: String,
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
            this.$emit('button-click', idAr, this.role);
        }
    }
})

Vue.component ("doc-button-arr", {
    props: {
        id: Array,
        title: String,
        icon: String,
        zipname: String,
    },
    template: `
    <div class="trca-docs-content-button" :title="title" @click="buttonClick">
        <i class="material-icons">
            {{ icon }}
        </i>
    </div>`,
    methods : {
        buttonClick: function() {
            this.$emit('button-click', this.id, this.zipname);
        }
    }
})

Vue.component ("docs-upload-file", {
    props: {
        maxsize: String,
        title: String,
        value: String
    },
    template: `
    <div class="trca-docs-footer">
        <form enctype="multipart/form-data" method="POST"  id="trca-docs-footer-upload">
            <div class="trca-docs-footer-upload-button">
                <input id="trca-docs-footer-upload-input" class="trca-docs-footer-upload-input"
                       data-id="data-upload" multiple
                       name="tr_ca_upload_comp_by_user[]" type="file" v-on:change="buttonClick">
                {{ title }}
            </div>
        </form>
    </div>`,
    methods: {
        buttonClick: function(event) {
            let onFailure = () => { $('#trca-docs-footer-upload-input').val(null) };
            let onSuccess = () => {trustedCA.reloadDoc()};

            let name = "USER";
            let value = this.value;
            var props = [
                [name, value]
            ];

            trustedCA.multipleUpload(event.target.files, props, this.maxsize, onSuccess, onFailure);
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
            <div class="material-icons" :title="title" @click="showDocMenu" data-id="data-menu">
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
        title: String,
        icon: String,
        id: Number,
    },
    template: `
    <div class="trca-docs-menu-button" :title="title" @click="buttonClick">
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

