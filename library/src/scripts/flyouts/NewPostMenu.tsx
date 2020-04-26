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
import { ColorHelper, deg, rotate, scale, translate } from "csx";
import * as easings from "d3-ease";
import { negativeUnit, unit } from "@library/styles/styleHelpers";

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
    const [hover, setHover] = useState(false);
    const [focus, setFocus] = useState(false);
    const animationVars = newPostMenuVariables().animation;
    const animationConfig = { duration: animationVars.time, easing: easings.easeQuadOut };

    const shadowVars = shadowVariables().newPostButton;
    const classes = newPostMenuClasses();

    const icon = (
        <Spring
            to={{ transform: !open ? rotate("0deg") : rotate(deg(animationVars.twist.deg)) }}
            config={animationConfig}
        >
            {transform => {
                return (
                    <animated.span className={classes.animationWrapper} style={transform}>
                        <NewPostMenuIcon />
                    </animated.span>
                );
            }}
        </Spring>
    );

    return (
        <div className={classNames(classes.root)}>
            <Spring
                to={
                    open
                        ? {
                              ...animationVars.open.shadow,
                              x: unit(0),
                              y: unit(0),
                              scale: animationVars.open.scale,
                          }
                        : {
                              ...animationVars.closed.shadow,
                              x: negativeUnit(shadowVars.horizontalOffset),
                              y: negativeUnit(shadowVars.verticalOffset),
                              scale: animationVars.closed.scale,
                          }
                }
                config={animationConfig}
            >
                {animationProps => {
                    const animatedStyles = {
                        transform: `${translate(animationProps.x, animationProps.y)} ${scale(animationProps.scale)}`,
                        ...shadowHelper().newPostButton({
                            verticalOffset: animationProps.verticalOffset,
                            horizontalOffset: animationProps.horizontalOffset,
                            blur: animationProps.blur,
                            spread: animationProps.spread,
                            opacity: animationProps.opacity,
                        }),
                    };
                    return (
                        <div className={classNames(classes.animationWrapper, classes.domStates)}>
                            <animated.span className={classes.animationWrapper} style={animatedStyles}>
                                <Button
                                    baseClass={ButtonTypes.CUSTOM}
                                    onClick={() => {
                                        setOpen(!open);
                                    }}
                                    className={classNames(classes.toggle, {
                                        [classes.isOpen]: open,
                                    })}
                                >
                                    {icon}
                                </Button>
                            </animated.span>
                        </div>
                    );
                }}
            </Spring>
            {open && <NewPostMenuItems {...props} />}
        </div>
    );
}
