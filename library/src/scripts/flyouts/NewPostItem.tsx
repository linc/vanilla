import React, { CSSProperties } from "react";
import classNames from "classnames";

import LinkAsButton from "@library/routing/LinkAsButton";
import Button from "@library/forms/Button";
import { ButtonTypes } from "@library/forms/buttonTypes";
import { PostTypes, IAddPost } from "./NewPostMenu";
import { newPostMenuClasses } from "@library/flyouts/newPostItemsStyles";

export default function NewPostItem(props: {
    item: IAddPost;
    style: CSSProperties;
    exitHandler?: () => void;
    parentAnimationCompleted: boolean;
    open: boolean;
}) {
    const { action, className, type, label, icon } = props.item;
    const { style, exitHandler = () => {} } = props;
    const classes = newPostMenuClasses();

    const contents = (
        <>
            {icon}
            <span className={classes.label}>{label}</span>
        </>
    );

    return (
        <div className={classNames(classes.item)}>
            {type === PostTypes.BUTTON ? (
                <Button
                    baseClass={ButtonTypes.CUSTOM}
                    className={classNames(className, classes.action)}
                    onClick={() => {
                        if (typeof action !== "string") {
                            action();
                        }
                        exitHandler();
                    }}
                    style={style}
                >
                    {contents}
                </Button>
            ) : (
                <LinkAsButton
                    baseClass={ButtonTypes.CUSTOM}
                    className={classNames(className, classes.action)}
                    to={action as string}
                    style={style}
                    onClick={exitHandler}
                >
                    {contents}
                </LinkAsButton>
            )}
        </div>
    );
}
