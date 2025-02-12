/**
 * @author Jenny Seburn <jseburn@higherlogic.com>
 * @copyright 2009-2024 Vanilla Forum Inc.
 * @license Proprietary
 */

import { IReaction } from "@dashboard/@types/api/reaction";
import { IUserFragment } from "@library/@types/api/users";
import { useReactionLog, useToggleReaction } from "@library/postReactions/PostReactions.hooks";
import { IPostReaction, IPostRecord } from "@library/postReactions/PostReactions.types";
import { PropsWithChildren, createContext, useContext, useEffect } from "react";

export const PostReactionsContext = createContext<
    IPostRecord & {
        toggleReaction?: (props: { reaction?: IReaction; user?: IUserFragment; deleteOnly?: boolean }) => void;
        reactionLog?: IPostReaction[];
        getUsers?: (tagID: number) => IUserFragment[];
        counts?: Array<Partial<IReaction>>;
    }
>({});

export function usePostReactionsContext() {
    return useContext(PostReactionsContext);
}

export function PostReactionsProvider(props: PropsWithChildren<IPostRecord>) {
    const { children, ...record } = props;
    const reactionLog = useReactionLog(record);
    const { toggleReaction, toggleResponse } = useToggleReaction(record);

    const getUsers = (tagID: number): IUserFragment[] => {
        return (reactionLog?.data ?? []).filter((reaction) => reaction.tagID === tagID).map(({ user }) => user);
    };

    return (
        <PostReactionsContext.Provider
            value={{
                ...record,
                reactionLog: reactionLog.data,
                getUsers,
                toggleReaction,
                counts: toggleResponse,
            }}
        >
            {children}
        </PostReactionsContext.Provider>
    );
}
