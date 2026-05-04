(function (blocks, blockEditor, components, element, i18n, serverSideRender) {
    const el = element.createElement;
    const InspectorControls = blockEditor.InspectorControls;
    const PanelBody = components.PanelBody;
    const TextControl = components.TextControl;
    const ServerSideRender = serverSideRender;
    const __ = i18n.__;

    blocks.registerBlockType('partner-organizations/partner-directory', {
        apiVersion: 3,
        title: __('Partner Directory', 'partner-organizations'),
        category: 'widgets',
        icon: 'groups',
        description: __('Insert a Partner Directory that displays published Partner Organizations, optionally filtered by Partner Category slug.', 'partner-organizations'),
        attributes: {
            categorySlug: {
                type: 'string',
                default: '',
            },
        },
        supports: {
            html: false,
        },
        edit: function (props) {
            const categorySlug = props.attributes.categorySlug || '';

            return el(
                element.Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        {
                            title: __('Partner Directory settings', 'partner-organizations'),
                            initialOpen: true,
                        },
                        el(TextControl, {
                            label: __('Partner Category slug', 'partner-organizations'),
                            help: __('Optional. Leave blank to show all published Partner Organizations.', 'partner-organizations'),
                            value: categorySlug,
                            onChange: function (value) {
                                props.setAttributes({ categorySlug: value });
                            },
                        })
                    )
                ),
                el(ServerSideRender, {
                    block: 'partner-organizations/partner-directory',
                    attributes: props.attributes,
                })
            );
        },
        save: function () {
            return null;
        },
    });
})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.element,
    window.wp.i18n,
    window.wp.serverSideRender
);
