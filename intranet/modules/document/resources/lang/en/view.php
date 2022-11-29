<?php

return [
    'Add accounts' => 'Thêm tài khoản',
    'Attach file' => 'Attachments',
    'Approver' => 'Approver',
    'Author' => 'Request creator',
    'Back' => 'Back',
    'Changed' => 'Changed',
    'Add' => 'Add',
    'Close' => 'Close',
    'Coordinator' => 'Coordinator',
    'Comment' => 'Comment',
    'Content' => 'Content',
    'Create' => 'Create',
    'Created document' => 'Document creation',
    'Create type' => 'Add',
    'Create document' => 'Add',
    'Current version' => 'Current version',
    'Created time' => 'Created time',
    'Delete' => 'Delete',
    'Document' => 'Document',
    'Document code' => 'Document code',
    'Document name' => 'Document name',
    'Document creator' => 'Document creator',
    'Document request' => 'Document request',
    'Document help' => 'Document help',
    'Help' => 'Help',
    'Description' => 'Description',
    'Download' => 'Download',
    'Email subject' => 'Email subject',
    'Email content' => 'Email content',
    'Edit' => 'Edit',
    'Edit type' => 'Edit type',
    'Edit document' => 'Edit document',
    'Feedback reason' => 'Feedback reason',
    'Document file' => 'Document file',
    'Document types' => 'Document types',
    'File name' => 'File name',
    'File upload' => 'File upload',
    'Or input link' => 'Enter file url',
    'Group' => 'Group',
    'History' => 'History',
    'Mimetype' => 'Mimetype',
    'List versions' => 'List versions',
    'Name' => 'Name',
    'Not assigne' => 'Yet to be assigned',
    'Note' => 'Note',
    'Parent' => 'Parent',
    'Publish document' => 'Publish a document',
    'Reviewer' => 'Reviewer',
    'Save' => 'Save',
    'Send mail' => 'Send mail',
    'show less' => 'show less',
    'show more' => 'show more',
    'Status' => 'Status',
    'Sort order' => 'Sort order',
    'Search' => 'Search',
    'Submit' => 'Submit',
    'Set as current' => 'Set as current',
    'Title' => 'Title',
    'Type file' => 'Type file',
    'Unapproved Value' => 'Unapproved Value',
    'Upload file' => 'Upload file',
    'Version' => 'Version',
    'Version manage' => 'Version manage',
    'mail_publish' => [
        'subject' => 'Notice of document issuance :code',
        'content' => '<p><strong>Dear {{ name }},</strong></p>'
            . '<p>Document <strong>:code</strong> has been issued</p>'
            . '<p><a href=":link">Detail</a></p>'
    ],
    'Publisher' => 'Publisher',
    /** request **/
    'Request name' => 'Request name',
    'Created document request' => 'Created a document request',
    'Create document request' => 'Create a document request',
    'Edit document request' => 'Edit a document request',
    'All documnets' => 'All documnets',
    'Account' => 'Account',
    'Published to' => 'Published to',
    'Re publish document' => 'Republish the document',
    'Re-publish' => 'Republish the document',
    'File document' => 'File document',
    'search for' => 'search for',
    'Editor' => 'Editor',
    'Select all' => 'Select all',
    'View suggest reviewers' => 'View suggest reviewers',
    'Choose reviewer and click save' => 'Choose reviewers and remember to select "Save" changes',
    'Belong to group' => 'Documents of the department',
    'Creator' => 'Creator',
    'View by team' => 'View by team',
    'Publish to' => 'Publish to',
    'Team publish' => 'Team publish',
    'Type' => 'Type',
    'Team' => 'Team',
    'Publish' => 'Publish',
    'Feedback' => 'Feedback',
    'guide_create_doc' => '<ul style="line-height: 25px;">
                    <li>Document request: need to create a previous document request to select this item</li>
                    <li>Document code: enter the document code to create</li>
                    <li>Document file: can choose to upload file or enter document file path</li>
                    <li>Attachment: choose an attachment, may or may not</li>
                    <li>Group: integration of team / division documents</li>
                    <li>Document type: select the document type to select, if no document type is available, click the "+" button to add it</li>
                    <li>Finally choose
                        <ul>
                            <li>Save: to save changes (saved temporarily, can be edited again)</li>
                            <li>Submit: to submit the document transfer to the Coordinator (after submitting it cannot be edited)li>
                        </ul>
                    </li>
                </ul>',
    'guide_document' => '<h4><b>Process</b></h4>
                <ol style="line-height: 25px;">
                    <li>Create a request to issue a document</li>
                    <li>Request documents submitted to COO approve</li>
                    <li>Browse the request to issue the document, if any feedback is returned to the request creator</li>
                    <li>The person requested to create the document</li>
                    <li>The document is passed to the coodinator to select reviewers</li>
                    <li>Person selected review document, if any feedback is returned to the document creator</li>
                    <li>The document is passed back to the coordinator to choose who approve the document</li>
                    <li>The selected person approve the document, if any feedback is returned to the document creator</li>
                    <li>The document is passed back to the coordinator to select the publisher of the document</li>
                    <li>The selected person issues the document, if any feedback is returned to the document creator</li>
                </ol>
                <p style="color: #3333ff;"><i>Coordinator can be accessed via System data -> tab General -> Document coordinator</i></p>',
    'guide_publish_document' => '<h4><b>Publish a document</b></h4>
                <ul style="line-height: 25px;">
                    <li>A pop up will be shown when selecting Publish.</li>
                    <li>Select a group or an account which will receive notifications.</li>
                    <li>Please enter notification title</li>
                    <li>Send email</li>
                    <li>Pressing Send email will publish selected documents. Documents can be republished via Re-publish button.</li>
                </ul>',
    'Handbook document' => 'Handbook document',
    'Create' => 'Create',
    'Update' => 'Update',
    'Add image' => 'Add image',
    'Add document and click save' => 'Add document and remember to select "Create"',
    'Edit document and click save' => 'Edit document and remember to select "Update"',
];