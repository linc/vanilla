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

    const [{ size }, setSize] = useSpring(() => ({
        size: 1,
    }));

    // const [{ horizontalOffset, verticalOffset, blur, spread }, setShadow] = useSpring(() => ({
    //     horizontalOffset: shadowVars.horizontalOffset,
    //     verticalOffset: shadowVars.verticalOffset,
    //     blur: shadowVars.blur,
    //     spread: shadowVars.spread,
    // }));

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

    useEffect(() => {
        setSize({
            size: hover ? 1 : open ? animationVars.open.scale : animationVars.closed.scale,
        });
        // setShadow({
        //     horizontalOffset: open
        //         ? animationVars.open.horizontalOffset
        //         : animationVars.closed.horizontalOffset,
        //     verticalOffset: open ? animationVars.open.verticalOffset : animationVars.closed.verticalOffset,
        //     blur: open ? animationVars.open.blur : animationVars.closed.blur,
        //     spread: open ? animationVars.open.spread : animationVars.closed.spread,
        // });
    }, [open, hover]);

    return (
        <div className={classNames(classes.root)}>
            <Spring
                to={{
                    scale: scale(size.value),
                    // horizontalOffset: horizontalOffset.value,
                    // verticalOffset: verticalOffset.value,
                    // blur: blur.value,
                    // spread: spread.value,
                }}
                config={animationConfig}
            >
                {scaleProps => {
                    return (
                        <>
                            <Spring
                                to={
                                    !open
                                        ? {
                                              ...animationVars.open.shadow,
                                              x: negativeUnit(shadowVariables().newPostButton.horizontalOffset),
                                              y: negativeUnit(shadowVariables().newPostButton.verticalOffset),
                                          }
                                        : {
                                              ...animationVars.closed.shadow,
                                              x: unit(0),
                                              y: unit(0),
                                          }
                                }
                                config={animationConfig}
                            >
                                {shadowProps => {
                                    return (
                                        <animated.span
                                            className={classes.animationWrapper}
                                            style={{
                                                transform: `${translate(shadowProps.x, shadowProps.y)} ${
                                                    scaleProps.scale
                                                }`,
                                                ...shadowHelper().newPostButton({
                                                    verticalOffset: shadowProps.verticalOffset,
                                                    horizontalOffset: shadowProps.horizontalOffset,
                                                    blur: shadowProps.blur,
                                                    spread: shadowProps.spread,
                                                    opacity: shadowProps.opacity,
                                                }),
                                            }}
                                        >
                                            <Button
                                                baseClass={ButtonTypes.CUSTOM}
                                                onClick={() => {
                                                    setOpen(!open);
                                                }}
                                                className={classNames(classes.toggle, {
                                                    [classes.isOpen]: open,
                                                })}
                                                onMouseEnter={() => {
                                                    setHover(true);
                                                }}
                                                onMouseLeave={() => {
                                                    setHover(false);
                                                }}
                                            >
                                                {icon}
                                            </Button>
                                        </animated.span>
                                    );
                                }}
                            </Spring>
                            {open && <NewPostMenuItems {...props} />}
                        </>
                    );
                }}
            </Spring>
        </div>
    );
}
