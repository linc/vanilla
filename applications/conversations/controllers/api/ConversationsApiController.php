<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license GPLv2
 */

use Garden\Schema\Schema;
use Garden\Web\Data;
use Garden\Web\Exception\NotFoundException;
use Garden\Web\Exception\ServerException;
use Vanilla\Utility\CapitalCaseScheme;

/**
 * API Controller for the `/conversations` resource.
 */
class ConversationsApiController extends AbstractApiController {

    /** @var CapitalCaseScheme */
    private $caseScheme;

    /** @var ConversationModel */
    private $conversationModel;

    /** @var UserModel */
    private $userModel;

    /**
     * ConversationsApiController constructor.
     *
     * @param ConversationModel $conversationModel
     * @param UserModel $userModel
     */
    public function __construct(ConversationModel $conversationModel, UserModel $userModel) {
        $this->caseScheme = new CapitalCaseScheme();
        $this->conversationModel = $conversationModel;
        $this->userModel = $userModel;
    }

    /**
     * Check that the user has moderation rights over conversations.
     *
     * @throw Exception
     */
    private function checkModerationPermission() {
        if (!c('ConversationMessages.Moderation.Allow', false)) {
            throw permissionException();
        }
        $this->permission('ConversationMessages.Moderation.Manage');
    }

    /**
     * Get a conversation by its numeric ID.
     *
     * @param int $id The conversation ID.
     * @param int|null $viewingUserID The user viewing the conversation. Should only be set if user is part of the conversation.
     * @throws NotFoundException if the conversation could not be found.
     * @return array
     */
    private function conversationByID($id, $viewingUserID = null) {
        if ($viewingUserID) {
            $options = ['viewingUserID' => $viewingUserID];
        } else {
            $options = [];
        }

        $conversation = $this->conversationModel->getID($id, DATASET_TYPE_ARRAY, $options);
        if (!$conversation) {
            throw new NotFoundException('Conversation');
        }

        return $conversation;
    }

//
//    Uncomment and test once ConversationModel::delete() is properly implemented.
//    See https://github.com/vanilla/vanilla/issues/5897 for details.
//
//    /**
//     * Delete a conversation.
//     *
//     * @param int $id The ID of the conversation.
//     * @≠=throws NotFoundException if the conversation could not be found.
//     * @throws MethodNotAllowedException if Conversations.Moderation.Allow !== true.
//     */
//    public function delete($id) {
//        if (!c('Conversations.Moderation.Allow', false)) {
//            throw new MethodNotAllowedException();
//        }
//
//        $this->permission('Conversations.Moderation.Manage');
//
//        $this->schema(['id:i' => 'The conversation ID'])->setDescription('Delete a conversation.');
//        $this->schema([], 'out');
//
//        $this->conversationByID($id);
//        $this->conversationModel->deleteID($id);
//    }


    /**
     * Leave a conversation.
     *
     * @param int $id The ID of the conversation.
     * @throws NotFoundException if the conversation could not be found.
     * @return Data
     */
    public function delete_leave($id) {
        $this->permission('Garden.SignIn.Allow');

        $this->idParamSchema()->setDescription('Leave a conversation.');
        $this->schema([], 'out');

        $this->conversationByID($id);

        $this->conversationModel->clear($id, $this->getSession()->UserID);

        return new Data([], 204);
    }

    /**
     * Get the schema definition comprised of all available conversation fields.
     *
     * @return Schema
     */
    private function fullSchema() {
        $schemaDefinition = [
            'conversationID:i' => 'The ID of the conversation.',
            'name:s?' => 'The name of the conversation.',
            'dateInserted:dt' => 'When the conversation was created.',
            'insertUserID:i' => 'The user that created the conversation.',
            'insertUser:o?' => $this->getUserFragmentSchema(),
            'countParticipants:i' => 'The number of participants on the conversation.',
            'countMessages:i' => 'The number of messages on the conversation.',
            'countReadMessages:n|i?' => 'The number of unread messages by the current user on the conversation.',
            'dateLastViewed:n|dt?' => 'When the conversation was last viewed by the current user.',
        ];

        // We unset to preserve the order of the parameters.
        if (!c('Conversations.Subjects.Visible', false)) {
            unset($schemaDefinition['name:s?']);
        }

        static $schemaInitialized = false;
        if (!$schemaInitialized) {
            $schemaInitialized = true;
            $schema = $this->schema($schemaDefinition, 'Conversations');
        } else {
            $schema = Schema::parse($schemaDefinition);
        }

        return $schema;
    }

    /**
     * Get a conversation.
     *
     * @param int $id The ID of the conversation.
     * @throws NotFoundException if the conversation could not be found.
     * @return Data
     */
    public function get($id) {
        $this->permission('Conversations.Conversations.Add');

        $this->idParamSchema()->setDescription('Get a conversation.');
        $out = $this->schema($this->fullSchema(), 'out');

        $isInConversation = $this->conversationModel->inConversation($id, $this->getSession()->UserID);
        if ($isInConversation) {
            $viewingUserID = $this->getSession()->UserID;
        } else {
            $viewingUserID = null;
        }

        $conversation = $this->conversationByID($id, $viewingUserID);
        $data = [&$conversation];
        $this->conversationModel->joinParticipants($data, 1000);

        // We check for the moderation permission after we get the conversation to make sure that it actually exists.
        if (!$isInConversation) {
            $this->checkModerationPermission();
        }

        $this->userModel->expandUsers($conversation, ['InsertUserID']);

        $conversation = $this->normalizeConversation($conversation);

        return $out->validate($conversation);
    }

    /**
     * Get the conversation participants.
     *
     * @param int $id The ID of the conversation.
     * @param array $query The query string.
     * @throws NotFoundException if the conversation could not be found.
     * @return Data
     */
    public function get_participants($id, array $query) {
        $this->permission('Conversations.Conversations.Add');

        $this->idParamSchema();

        $in = $this->schema([
            'page:i?' => [
                'description' => 'Page number.',
                'default' => 1,
                'minimum' => 1,
            ],
            'limit:i?' => [
                'description' => 'The number of items per page.',
                'default' => 5,
                'minimum' => 5,
                'maximum' => 100
            ],
            'expand:b?' => 'Expand associated records.',
        ], 'in')->setDescription('Get participants of a conversation.');
        $out = $this->schema([
            'participants:a' => [
                'items' => [
                    'type'  => 'object',
                    'properties' => [
                        'userID' => [
                            'type' => 'integer',
                            'description' => 'The ID of the participant.',
                        ],
                        'user' => $this->getUserFragmentSchema(),
                        'deleted' => [
                            'type' => 'boolean',
                            'description' => 'True if the user left the conversation.',
                        ],
                    ],
                ],
                'description' => 'List of participants.',
            ]
        ], 'out');

        $query = $in->validate($query, true);

        $this->conversationByID($id);

        list($offset, $limit) = offsetLimit("p{$query['page']}", $query['limit']);

        $conversationMembers = $this->conversationModel->getConversationMembers($id, false, $limit, $offset);

        $data = [
            'participants' => array_values($conversationMembers),
        ];

        if (!empty($query['expand'])) {
            $this->userModel->expandUsers($data['participants'], ['UserID']);
        }

        return $out->validate($data);
    }

    /**
     * List conversations of a user.
     *
     * @param array $query The query string.
     * @return array
     */
    public function index(array $query) {
        $this->permission('Conversations.Conversations.Add');

        $in = $this->schema([
                'participantID:i?' => 'Filter by specified participating user instead of by current user.',
                'page:i?' => [
                    'description' => 'Page number.',
                    'default' => 1,
                    'minimum' => 1,
                ],
                'limit:i?' => [
                    'description' => 'The number of items per page.',
                    'default' => c('Conversations.Conversations.PerPage', 50),
                    'minimum' => 1,
                    'maximum' => 100
                ],
                'expand:b?' => 'Expand associated records.'
            ], 'in')
            ->setDescription('List user conversations.');
        $out = $this->schema([':a' => $this->fullSchema()], 'out');

        $query = $this->filterValues($query);
        $query = $in->validate($query);

        if (!empty($query['participantID'])) {
            if ($query['participantID'] !== $this->getSession()->UserID) {
                $this->checkModerationPermission();
            }
            $userID = $query['participantID'];
        } else {
            $userID = $this->getSession()->UserID;
        }

        list($offset, $limit) = offsetLimit("p{$query['page']}", $query['limit']);

        $conversations = $this->conversationModel->get2($userID, $offset, $limit)->resultArray();

        if (!empty($query['expand'])) {
            $this->userModel->expandUsers($conversations, ['InsertUserID']);
        }

        return $out->validate($conversations, true);
    }

    /**
     * Get an ID-only conversation record schema.
     *
     * @return Schema Returns a schema object.
     */
    private function idParamSchema() {
        return $this->schema(['id:i' => 'The conversation ID.'], 'in');
    }

    /**
     * Add a conversation.
     *
     * @param array $body The request body.
     * @throws ServerException If the conversation could not be created.
     * @return Data
     */
    public function post(array $body) {
        $this->permission('Conversations.Conversations.Add');

        $in = $this->postSchema('in')->setDescription('Add a conversation.');
        $out = $this->schema($this->fullSchema(), 'out');

        $body = $in->validate($body);
        $conversationData = $this->normalizeInput($body);
        $conversationData = $this->caseScheme->convertArrayKeys($conversationData);

        $conversationID = $this->conversationModel->save($conversationData, ['ConversationOnly' => true]);
        $this->validateModel($this->conversationModel, true);
        if (!$conversationID) {
            throw new ServerException('Unable to insert conversation.', 500);
        }

        $conversation = $this->conversationByID($conversationID, $this->getSession()->UserID);
        return new Data(
            $out->validate($conversation),
            201
        );
    }

    /**
     * Add participants to a conversation.
     *
     * @param int $id The ID of the conversation.
     * @param array $body The request body.
     * @throws NotFoundException if the conversation could not be found.
     * @throws ServerException If the participants could not be added.
     * @return Data
     */
    public function post_participants($id, array $body) {
        $this->permission('Conversations.Conversations.Add');

        $this->idParamSchema();

        $in = $this->postSchema('in')->setDescription('Add participants to a conversation.');
        $this->schema([], 'out');

        $this->conversationByID($id);

        $body = $in->validate($body);

        $success = $this->conversationModel->addUserToConversation($id, $body['participantIDs']);
        if (!$success) {
            throw new ServerException('Unable to add participants.', 500);
        }

        return new Data([], 201);
    }

    /**
     * Get a post schema with minimal add/edit fields.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function postSchema($type) {
        static $postSchema;

        if ($postSchema === null) {
            $inSchema = [
                'participantIDs:a' => [
                    'items' => [
                        'type'  => 'integer',
                    ],
                    'description' => 'List of userID of the participants.',
                ],
                'name' => null,
            ];
            if (!c('Conversations.Subjects.Visible', false)) {
                unset($inSchema['name']);
            }

            $postSchema = $this->schema(
                Schema::parse($inSchema)->add($this->fullSchema()),
                'ConversationPost'
            );
        }

        return $this->schema($postSchema, $type);
    }

    /**
     * Normalize input parameters.
     *
     * @param array $input
     * @return array The normalized input.
     */
    private function normalizeInput(array $input) {
        if (array_key_exists('name', $input)) {
            $input['subject'] = $input['name'];
            unset($input['name']);
        }
        if (array_key_exists('participantIDs', $input)) {
            $input['recipientUserID'] = $input['participantIDs'];
            unset($input['participantIDs']);
        }

        return $input;
    }

    /**
     * Normalize conversation for output.
     *
     * @param array $conversation
     * @return array The normalized conversation.
     */
    private function normalizeConversation(array $conversation) {
        if (array_key_exists('subject', $conversation)) {
            $conversation['name'] = $conversation['subject'];
            unset($conversation['subject']);
        }

        return $conversation;
    }
}
