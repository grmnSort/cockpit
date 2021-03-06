<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Collections')</span></li>
    </ul>
</div>

<div riot-view>

    <div>

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(collections) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter collections...')" onkeyup="{ updatefilter }">

            </div>

            @hasaccess?('collections', 'create')
            <div class="uk-float-right">
                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/collections/collection')">@lang('Add Collection')</a>
            </div>
            @end

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(collections) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('collections:icon.svg')" width="80" height="80" alt="Collections" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No Collections').</strong>
                @hasaccess?('collections', 'create')
                <a href="@route('/collections/collection')">@lang('Create one')</a></span>
                @end
            </div>

        </div>

        <div class="uk-margin" if="{groups.length}">

            <ul class="uk-tab uk-flex uk-flex-center uk-noselect">
                <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize { group && 'uk-text-muted'}" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                <li class="{ group==parent.group && 'uk-active'}" each="{group in groups}"><a class="uk-text-capitalize { group!=parent.group && 'uk-text-muted'}" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
            </ul>
        </div>

        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-margin-top">

            <div each="{ collection, idx in collections }" show="{ ingroup(collection.meta) && infilter(collection.meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a href="@route('/collections/entries')/{collection.name}" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (collection.meta.color) }">
                                <img riot-src="{ collection.meta.icon ? '@url('assets:app/media/icons/')'+collection.meta.icon : '@url('collections:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="delay:300">

                            <a class="uk-icon-cog" style="color:{ (collection.meta.color) }" href="@route('/collections/collection')/{ collection.name }" if="{ collection.meta.allowed.edit }"></a>
                            <a class="uk-icon-cog" style="color:{ (collection.meta.color) }" if="{ !collection.meta.allowed.edit }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/collections/entries')/{collection}">@lang('Entries')</a></li>
                                    <li><a href="@route('/collections/entry')/{collection}" if="{ collection.meta.allowed.entries_create }">@lang('Add entry')</a></li>
                                    <li if="{ collection.meta.allowed.edit || collection.meta.allowed.delete }" class="uk-nav-divider"></li>
                                    <li if="{ collection.meta.allowed.edit }"><a href="@route('/collections/collection')/{ collection.name }">@lang('Edit')</a></li>
                                    @hasaccess?('collections', 'delete')
                                    <li class="uk-nav-item-danger" if="{ collection.meta.allowed.delete }"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    @end
                                    <li class="uk-nav-divider" if="{ collection.meta.allowed.edit }"></li>
                                    <li class="uk-text-truncate" if="{ collection.meta.allowed.edit }"><a href="@route('/collections/export')/{ collection.name }" download="{ collection.meta.name }.collection.json">@lang('Export entries')</a></li>
                                    <li class="uk-text-truncate" if="{ collection.meta.allowed.edit }"><a href="@route('/collections/import/collection')/{ collection.name }">@lang('Import entries')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-text-center uk-link-muted" href="@route('/collections/entries')/{collection.name}">{ collection.label }</a>
                        <div>
                            <span class="uk-badge" riot-style="background-color:{ (collection.meta.color) }">{ collection.meta.itemsCount }</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.collections = {{ json_encode($collections) }};
        this.groups = [];

        this.collections.forEach(function(collection) {

            if (collection.meta.group) {
                $this.groups.push(collection.meta.group);
            }
        });

        if (this.groups.length) {
            this.groups = _.uniq(this.groups.sort());
        }


        remove(e, collection) {

            collection = e.item.collection;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('collections:removeCollection', collection.name).then(function(data) {

                    App.ui.notify("Collection removed", "success");

                    $this.collections.splice(e.item.idx, 1);

                    $this.groups = [];

                    $this.collections.forEach(function(collection) {
                        if (collection.meta.group) $this.groups.push(collection.meta.group);
                    });

                    if ($this.groups.length) {
                        $this.groups = _.uniq($this.groups.sort());
                    }

                    $this.update();
                });
            });
        }

        toggleGroup(e) {
            this.group = e.item && e.item.group || false;
        }

        updatefilter(e) {

        }

        ingroup(singleton) {
            return this.group ? (this.group == singleton.group) : true;
        }

        infilter(collection, value, name, label) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [collection.name.toLowerCase(), collection.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
