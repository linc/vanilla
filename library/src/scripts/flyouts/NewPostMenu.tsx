import React, { ReactNode, useEffect, useState } from "react";
import classNames from "classnames";

import { NewPostMenuIcon } from "@library/icons/common";
import LinkAsButton from "@library/routing/LinkAsButton";
import Button from "@library/forms/Button";
import { ButtonTypes } from "@library/forms/buttonTypes";
import { newPostMenuClasses, newPostMenuVariables } from "@library/flyouts/newPostMenuStyles";
import { useSpring, animated } from "react-spring";
import { Spring } from "react-spring/renderprops";
import { shadowHelper, shadowVariables } from "@library/styles/shadowHelpers";
import { deg, rotate, scale } from "csx";
import * as easings from "d3-ease";

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
}

function ActionItem(props: IAddPost) {
    const { action, className, type, label, icon } = props;
    const classes = newPostMenuClasses();

    const contents = (
        <>
            {icon}
            <span className={classes.label}>{label}</span>
        </>
    );

    return type === PostTypes.BUTTON ? (
        <Button onClick={action as () => void} className={classNames(className, classes.action)}>
            {contents}
        </Button>
    ) : (
        <LinkAsButton to={action as string} className={classNames(className, classes.action)}>
            {contents}
        </LinkAsButton>
    );
}

export function NewPostMenuItems(props: { items: IAddPost[] | [] }) {
    const { items } = props;

    if (!items || items.length === 0) {
        return null;
    }
    const classes = newPostMenuClasses();
    return (
        <div className={classes.menu}>
            {(items as []).map((action, i) => {
                return <ActionItem key={i} {...action} />;
            })}
        </div>
    );
}

export default function NewPostMenu(props: { items: IAddPost[] | [] }) {
    const { items } = props;
    const [open, setOpen] = useState(false);
    const animationVars = newPostMenuVariables().animation;
    const shadowVars = shadowVariables().newPostButton;
    const classes = newPostMenuClasses();

    const [{ horizontalOffset, verticalOffset, blur, spread, size }, buttonSetter] = useSpring(() => ({
        horizontalOffset: shadowVars.horizontalOffset,
        verticalOffset: shadowVars.verticalOffset,
        blur: shadowVars.blur,
        spread: shadowVars.spread,
        size: 1,
    }));

    const toggle = () => {
        setOpen(!open);
    };

    return (
        <div className={classNames(classes.root)}>
            <Button
                baseClass={ButtonTypes.CUSTOM}
                onClick={toggle}
                className={classNames(classes.toggle, {
                    [classes.isOpen]: open,
                })}
                style={{
                    ...shadowHelper().newPostButton({
                        horizontalOffset: horizontalOffset.value,
                        verticalOffset: verticalOffset.value,
                        blur: blur.value,
                        spread: spread.value,
                    }),
                    transform: scale(size.value),
                }}
                // onMouseEnter={() => {
                //     set({
                //         horizontalOffset: 0,
                //         verticalOffset: 0,
                //         blur: 0,
                //         spread: 0,
                //         transform: scale(size.value),
                //     });
                // }}
            >
                // @ts-ignore
                <Spring
                    to={{ transform: !open ? rotate("0deg") : rotate(deg(animationVars.twist.deg)) }}
                    config={{ duration: animationVars.twist.time, easing: easings.cubicOut }}
                >
                    {transform => {
                        return (
                            <animated.span className={classes.animationWrapperIcon} style={transform}>
                                <NewPostMenuIcon />
                            </animated.span>
                        );
                    }}
                </Spring>
            </Button>
            {open && <NewPostMenuItems {...props} />}
        </div>
    );
}
