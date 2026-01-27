/// <reference types="vite/client" />

/**
 * This is required for webpack to correctly import vue file when using TypeScript.
 */ 
declare module '*.vue' {
    const content: any;
    export default content;
}