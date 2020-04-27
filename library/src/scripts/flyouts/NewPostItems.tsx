import React, { ReactNode, useState } from "react";
import classNames from "classnames";

import { newPostMenuClasses } from "@library/flyouts/newPostItemsStyles";
import { Trail } from "react-spring/renderprops";
import NewPostItem from "./NewPostItem";
import Modal from "@library/modal/Modal";
import ModalSizes from "@library/modal/ModalSizes";
import SiteNavProvider from "@library/navigation/SiteNavContext";

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

export interface ITransition {
    opacity: number;
    transform: string;
}

export default function NewPostItems(props: { items: IAddPost[] }) {
    const classes = newPostMenuClasses();
    const { items, } = props;

    return (

            <>
                {/*<div className={classes.items}>*/}
                {/*    {items.map( item => {*/}
                {/*        return <NewPostItem item={item}/>;*/}
                {/*    })}*/}
                {/*</div>*/}



                <Trail
                    config={{ mass: 2, tension: 3000, friction: 150 }}
                    items={items}
                    from={{ opacity: 0, transform: "translate3d(0, 100%, 0)" }}
                    to={{ opacity: 1, transform: "translate3d(0, 0, 0)" }}
                >
                    {item => (props) => <NewPostItem item={item} style={props} />}
                </Trail>
            </>
    );
}
