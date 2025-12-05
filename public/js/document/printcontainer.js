/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

opendxp.registerNS("opendxp.document.printcontainer");
/**
 * @private
 */
opendxp.document.printcontainer = Class.create(opendxp.document.printabstract, {
    type: "printcontainer",

    init: function () {

        var user = opendxp.globalmanager.get("user");

        if (this.isAllowed("settings")) {
            this.settings = new opendxp.document.snippets.settings(this, "printpage");
        }

        if (user.isAllowed("notes_events")) {
            this.notes = new opendxp.element.notes(this, "document");
        }

        if (this.isAllowed("properties")) {
            this.properties = new opendxp.document.properties(this, "document");
        }
        if (this.isAllowed("versions")) {
            this.versions = new opendxp.document.versions(this);
        }

        this.pdfpreview = new opendxp.document.printpages.pdfpreview(this);
        this.workflows = new opendxp.element.workflows(this, "document");
    },

    getTabPanel: function () {

        var items = [];
        var user = opendxp.globalmanager.get("user");

        items.push(this.pdfpreview.getLayout());
        if (this.isAllowed("settings")) {
            items.push(this.settings.getLayout());
        }
        if (this.isAllowed("properties")) {
            items.push(this.properties.getLayout());
        }
        if (this.isAllowed("versions")) {
            items.push(this.versions.getLayout());
        }

        if (user.isAllowed("notes_events")) {
            items.push(this.notes.getLayout());
        }

        if (user.isAllowed("workflow_details") && this.data.workflowManagement && this.data.workflowManagement.hasWorkflowManagement === true) {
            items.push(this.workflows.getLayout());
        }

        this.tabbar = opendxp.helpers.getTabBar({items: items, defaults: {autoScroll:true}, height: 46});
        return this.tabbar;
    }
});

