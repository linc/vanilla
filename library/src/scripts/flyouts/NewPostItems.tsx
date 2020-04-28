import React, { ReactNode, useState } from "react";
import classNames from "classnames";

import { newPostMenuClasses } from "@library/flyouts/newPostItemsStyles";
import { Spring, Trail } from "react-spring/renderprops";
import NewPostItem from "./NewPostItem";
import ReactDOM from "react-dom";
import { DocumentationIcon } from "@library/icons/common";

export enum PostTypes {
    LINK = "link",
    BUTTON = "button",
}
export interface IAddPost {
    action: (() => void) | string;
    type: PostTypes;
    className?: string;
    label: string;
    icon: JSX.Element;
    id?: string;
}

export interface ITransition {
    opacity: number;
    transform: string;
}

export default function NewPostItems(props: {
    open: boolean;
    items: IAddPost[];
    firstRun: boolean;
    exitHandler?: () => void;
}) {
    const { open, items, exitHandler, firstRun } = props;
    const classes = newPostMenuClasses();

    // Generate keys for Trail, as it does render them on the fly correctly
    items.forEach((item, i) => {
        item.key = `${i}`;
    });

    const transitionState = "translate3d(0, 100%, 0)";
    const restState = "translate3d(0, 0, 0)";

    const from = firstRun
        ? undefined
        : {
              opacity: open ? 0 : 1,
              transform: open ? transitionState : restState,
          };

    const to = {
        opacity: !open ? 0 : 1,
        transform: !open ? transitionState : restState,
    };

    return (
        <div className={classes.items}>
            <Trail
                items={items}
                config={{ mass: 2, tension: 3000, friction: 150 }}
                from={from}
                to={to}
                keys={item => item.key}
            >
                {item => animatedProps => <NewPostItem style={animatedProps} item={item} exitHandler={exitHandler} />}
            </Trail>
        </div>
    );
}
