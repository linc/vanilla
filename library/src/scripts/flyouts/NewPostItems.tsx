import React, { useState } from "react";
import { newPostMenuClasses } from "@library/flyouts/newPostItemsStyles";
import { Trail } from "react-spring/renderprops";
import NewPostItem from "./NewPostItem";
import { unit } from "@library/styles/styleHelpers";

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
    key?: string;
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
    const [animationComplete, setAnimationComplete] = useState(false);

    // Generate keys for Trail, as it does render them on the fly correctly
    items.forEach((item, i) => {
        item.key = `${i}`;
    });

    const transitionState = `translate3d(0, ${unit(items.length * 100)}, 0)`;
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
        <div className={classes.itemsWrapper}>
            <div className={classes.items}>
                <Trail
                    items={items}
                    config={{ mass: 5, tension: 400, friction: 70 }}
                    from={from}
                    to={to}
                    reverse={!open}
                    keys={item => item.key}
                    onStart={() => {
                        setAnimationComplete(false);
                    }}
                    onRest={() => {
                        setAnimationComplete(true);
                    }}
                >
                    {item => animatedProps => (
                        <NewPostItem
                            parentAnimationCompleted={animationComplete}
                            open={open}
                            style={animatedProps}
                            item={item}
                            exitHandler={exitHandler}
                        />
                    )}
                </Trail>
            </div>
        </div>
    );
}
